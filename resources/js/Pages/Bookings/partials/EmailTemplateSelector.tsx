import React, { useState, useRef, useEffect } from "react";
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  MenuItem,
  Select,
  CircularProgress,
  Paper,
  IconButton,
  Tooltip,
  Chip,
  DialogContentText
} from "@mui/material";
import EmailEditor, { EditorRef, EmailEditorProps } from "react-email-editor";
import AttachFileIcon from "@mui/icons-material/AttachFile";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import FullscreenIcon from "@mui/icons-material/Fullscreen";
import FullscreenExitIcon from "@mui/icons-material/FullscreenExit";

const LANGUAGES = ["en", "es", "de"];

const EmailTemplateEditor: React.FC = ({ booking }) => {
  const [lang, setLang] = useState<string>("en");
  const [templates, setTemplates] = useState<string[]>([]);
  const [selectedTemplate, setSelectedTemplate] = useState<string>("");
  const [isDialogOpen, setIsDialogOpen] = useState<boolean>(false);
  const [isSending, setIsSending] = useState<boolean>(false);
  const [isConfirmDialogOpen, setIsConfirmDialogOpen] = useState<boolean>(false);
  const emailEditorRef = useRef<EditorRef | null>(null);
  const [attachments, setAttachments] = useState<File[]>([]);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isFullscreen, setIsFullscreen] = useState(false);

  const { showSnackbar } = useSnackbar();

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files) {
      setAttachments([...attachments, ...Array.from(event.target.files)]);
    }
  };

  const handleRemoveAttachment = (fileToRemove: File) => {
    setAttachments(attachments.filter((file) => file !== fileToRemove));
  };

  useEffect(() => {
    const fetchTemplates = async () => {
      setIsSending(true);
      try {
        const response = await fetch(`/get-email-templates?lang=${lang}`);
        const data = await response.json();
        setTemplates(data.templates);
        setSelectedTemplate("");
      } catch (error) {
        console.error("Error loading templates:", error);
      } finally {
        setIsSending(false);
      }
    };
    fetchTemplates();
  }, [lang]);

  const onEditorReady: EmailEditorProps["onReady"] = async (unlayer) => {
    if (!selectedTemplate) return;
    try {
      const response = await fetch(`/get-email-template?lang=${lang}&template_name=${selectedTemplate}`);
      const data = await response.json();
      if (data.design && typeof data.design === "object") {
        unlayer.loadDesign(data.design);
      } else {
        console.error("Invalid template format:", data);
      }
    } catch (error) {
      showSnackbar("Error loading template content", "error");
      console.error("Error loading template content:", error);
    }
  };

  const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
  };

  const onLoad: EmailEditorProps["onLoad"] = () => {
    const unlayer = emailEditorRef.current?.editor;
    if (unlayer) {
      console.log("Unlayer editor is ready!");
    }
  };

  const handleSendEmail = () => {
    setIsConfirmDialogOpen(false);
    setIsSending(true);

    const unlayer = emailEditorRef.current?.editor;
    if (!unlayer) return;

    unlayer.exportHtml(async (data) => {
      const { html } = data;
      const formData = new FormData();
      formData.append("lang", lang);
      formData.append("template_name", selectedTemplate);
      formData.append("email_content", html);
      formData.append("event_id", booking.event_id);
      formData.append("booking_id", booking.id);
      attachments.forEach((file) => formData.append("attachments", file));

      try {
        const response = await fetch("/send-email", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": getCsrfToken() },
          body: formData,
        });

        if (response.ok) {
          showSnackbar("✅ Email sent successfully!", "success");
          setAttachments([]);
        } else {
          showSnackbar("❌ Failed to send email.", "error");
          throw new Error("❌ Failed to send email.");
        }
      } catch (error) {
        showSnackbar("⚠️ Error sending email, please try again.", "error");
        //alert("⚠️ Error sending email, please try again.");
        console.error("Error sending email:", error);
      } finally {
        setIsSending(false);
      }
    });
  };

  return (
    <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
      {/* Language Selector */}
      <Select value={lang} onChange={(e) => setLang(e.target.value)} style={{ width: 150 }}>
        {LANGUAGES.map((language) => (
          <MenuItem key={language} value={language}>
            {language.toUpperCase()}
          </MenuItem>
        ))}
      </Select>

      {/* Template Selector */}
      {isSending ? (
        <CircularProgress />
      ) : (
        <Select
          value={selectedTemplate}
          onChange={(e) => setSelectedTemplate(e.target.value)}
          displayEmpty
          style={{ width: 250 }}
        >
          <MenuItem value="" disabled>
            Select an email template
          </MenuItem>
          {templates.map((template) => (
            <MenuItem key={template} value={template}>
              {template.replace(/([A-Z])/g, " $1").replace(/^./, (str) => str.toUpperCase())}
            </MenuItem>
          ))}
        </Select>
      )}

      <Button variant="contained" color="primary" onClick={() => setIsDialogOpen(true)} disabled={!selectedTemplate}>
        Edit & Send
      </Button>

      {/* Email Editor Dialog */}
      <Dialog open={isDialogOpen} onClose={() => setIsDialogOpen(false)} fullWidth maxWidth="lg" fullScreen={isFullscreen}>
        <DialogTitle>
          Edit Email Template
          <IconButton onClick={() => setIsFullscreen(!isFullscreen)} style={{ float: "right" }}>
            {isFullscreen ? <FullscreenExitIcon /> : <FullscreenIcon />}
          </IconButton>
        </DialogTitle>
        <DialogContent style={{ height: "100%", position: "relative", paddingBottom: "60px" }}>
        <div style={{ height: "100vh", display: "flex", flexDirection: "column" }}>
          <EmailEditor ref={emailEditorRef} onReady={onEditorReady}  onLoad={onLoad} options={{ projectId: 1234, displayMode: "email" }} style={{ flex: 1, height: "100%" }}/>
        </div>
          {/* Fixed Bottom Bar */}
          <Paper
            elevation={3}
            style={{
              position: "absolute",
              bottom: 0,
              left: 0,
              width: "100%",
              padding: "10px",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              backgroundColor: "#131313",
            }}
          >
            <div>
              {attachments.map((file, index) => (
                <Chip
                  key={index}
                  label={file.name}
                  onDelete={() => handleRemoveAttachment(file)}
                  style={{ marginRight: "5px" }}
                />
              ))}
            </div>
            <Tooltip title="Attach Files">
              <IconButton onClick={() => fileInputRef.current?.click()}>
                <AttachFileIcon />
              </IconButton>
            </Tooltip>
            <input
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              multiple
              ref={fileInputRef}
              style={{ display: "none" }}
              onChange={handleFileChange}
            />
          </Paper>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setIsDialogOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={() => setIsConfirmDialogOpen(true)} variant="contained" color="primary" disabled={isSending}>
            {isSending ? "Sending..." : "Send Email"}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Confirm Send Email Dialog */}
      <Dialog open={isConfirmDialogOpen} onClose={() => setIsConfirmDialogOpen(false)}>
        <DialogTitle>
          Confirm Send Email
        </DialogTitle>
        <DialogContent>
          <DialogContentText>Are you sure you want to send this email?</DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setIsConfirmDialogOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleSendEmail} color="primary" variant="contained" disabled={isSending}>
            Confirm
          </Button>
        </DialogActions>
      </Dialog>
    </div>
  );
};

export default EmailTemplateEditor;
