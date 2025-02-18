import React, { useState, useRef, useEffect } from "react";
import { Button, Dialog, DialogActions, DialogContent, DialogTitle, MenuItem, Select, CircularProgress } from "@mui/material";
import EmailEditor, { EditorRef, EmailEditorProps } from "react-email-editor";

const LANGUAGES = ["en", "es", "de"]; // Available languages

const EmailTemplateEditor: React.FC = () => {
  const [lang, setLang] = useState<string>("en");
  const [templates, setTemplates] = useState<string[]>([]);
  const [selectedTemplate, setSelectedTemplate] = useState<string>("");
  const [isDialogOpen, setIsDialogOpen] = useState<boolean>(false);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const emailEditorRef = useRef<EditorRef | null>(null);

  /** Fetch available email templates when the language changes */
  useEffect(() => {
    const fetchTemplates = async () => {
      setIsLoading(true);
      try {
        const response = await fetch(`/get-email-templates?lang=${lang}`);
        const data = await response.json();
        setTemplates(data.templates);
        setSelectedTemplate(""); // Reset selection when changing language
      } catch (error) {
        console.error("Error loading templates:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchTemplates();
  }, [lang]);

  /** Fetch and load selected template when the editor is ready */
  const onEditorReady: EmailEditorProps["onReady"] = async (unlayer) => {
    console.log("EmailEditor is ready!");

    if (!selectedTemplate) return;

    try {
      const response = await fetch(`/get-email-template?lang=${lang}&template_name=${selectedTemplate}`);
      const data = await response.json();
      
      if (data.design && typeof data.design === "object") {
        console.log("Loading design into Unlayer:", data.design);
        unlayer.loadDesign(data.design);
      } else {
        console.error("Invalid template format:", data);
      }
    } catch (error) {
      console.error("Error loading template content:", error);
    }
  };

  const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
  };

  /** Handle sending the email */
  const handleSendEmail = () => {
    const unlayer = emailEditorRef.current?.editor;
    if (!unlayer) return;

    unlayer.exportHtml(async (data) => {
      const { html } = data;
      console.log("Sending email with content:\n", html);

      try {
        const response = await fetch("/send-email", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(), // Add CSRF token here
          },
          body: JSON.stringify({
            lang,
            template_name: selectedTemplate,
            email_content: html,
            recipient: "leonardo@70000tons.com"
          }),
        });

        if (response.ok) {
          alert("Email sent successfully!");
        //  setIsDialogOpen(false);
        } else {
          throw new Error("Failed to send email.");
        }
      } catch (error) {
        console.error("Error sending email:", error);
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
      {isLoading ? (
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
      <Dialog open={isDialogOpen} onClose={() => setIsDialogOpen(false)} fullWidth maxWidth="lg">
        <DialogTitle>Edit Email Template</DialogTitle>
        <DialogContent style={{ height: "500px" }}>
          <EmailEditor ref={emailEditorRef} onReady={onEditorReady} />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setIsDialogOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleSendEmail} variant="contained" color="primary">
            Send Email
          </Button>
        </DialogActions>
      </Dialog>
    </div>
  );
};

export default EmailTemplateEditor;
