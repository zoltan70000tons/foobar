// @ts-nocheck
import React, { useState, useRef, useEffect, useCallback } from "react";
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
  DialogContentText,
  TextField,
  FormControl,
  Grid,
  Autocomplete,
} from "@mui/material";
import AttachFileIcon from "@mui/icons-material/AttachFile";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import FullscreenIcon from "@mui/icons-material/Fullscreen";
import FullscreenExitIcon from "@mui/icons-material/FullscreenExit";
import EditIcon from "@mui/icons-material/Edit";
import VisibilityIcon from "@mui/icons-material/Visibility";
import InsertPhotoIcon from "@mui/icons-material/InsertPhoto";
import PictureAsPdfIcon from "@mui/icons-material/PictureAsPdf";
import ReceiptIcon from '@mui/icons-material/Receipt';
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import UnlayerEditor from "@/Components/UnlayerEditor";
import type { Booking } from "@/types/booking";

const LANGUAGES = ["en", "es", "de"];

interface EmailTemplateEditorProps {
  booking: Booking;
  editMode: boolean;
  defaultLanguage?: string;
}

interface Passenger {
  id: number;
  email: string;
}

interface Template {
  id: number;
  name: string;
  subject: string;
  lang: string;
  priority: number;
  hidden: boolean;
}

const EmailTemplateEditor: React.FC<EmailTemplateEditorProps> = ({ booking, editMode, defaultLanguage = "en" }) => {
  const [lang, setLang] = useState<string>(defaultLanguage);
  const [templates, setTemplates] = useState<Template[]>([]);
  const [selectedTemplate, setSelectedTemplate] = useState<{
    id: number;
    name: string;
    subject: string;
    lang: string;
  } | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState<boolean>(false);
  const [isSending, setIsSending] = useState<boolean>(false);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [isConfirmDialogOpen, setIsConfirmDialogOpen] = useState<boolean>(false);
  const [attachments, setAttachments] = useState<File[]>([]);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [subject, setSubject] = useState<string>("");
  const [selectedPassenger, setSelectedPassenger] = useState<Passenger | null>(null);
  const [isCheckboxEnabled, setIsCheckboxEnabled] = useState(false);
  const [pdfFile, setPdfFile] = useState<File | null>(null);
  const [imgFile, setImgFile] = useState<File | null>(null);
  const [invoiceFile, setInvoiceFile] = useState<File | null>(null);
  const [templateInputValue, setTemplateInputValue] = useState<string>("");

  const { hasPermission } = usePermissions();

  const canSendEmail = !hasPermission(Permissions.SendEmails) || !editMode;

  const { showSnackbar } = useSnackbar();

  const handleLangChange = (value: string) => {
   setLang(value.toLowerCase().trim());
  };

  const fetchTemplates = useCallback(async () => {
    setIsSending(true);
    setSelectedTemplate(null);
    setTemplateInputValue("");
    setSelectedPassenger(null);
    setSubject("");
    setAttachments([]);
    setPdfFile(null);
    setImgFile(null);
    setInvoiceFile(null);
    try {
      const response = await fetch(`/get-email-templates?lang=${lang}`);
      const data = await response.json();
      const visibleTemplates = data.templates
        .filter((template) => !template.hidden)
        .sort((a, b) => {
          // Sort by priority first (ascending: 1 before 10)
          if (a.priority !== b.priority) {
            return a.priority - b.priority;
          }
          // If same priority, sort alphabetically
          return a.name.localeCompare(b.name);
        });

      setTemplates(visibleTemplates);
    } catch (error) {
      console.error("Error loading templates:", error);
    } finally {
      setIsSending(false);
    }
    if (window.unlayer) {
    try {
      window.unlayer.loadDesign({ body: { rows: [] } });
    } catch (e) {
      console.debug("unlayer reset failed", e);
    }
  }
  }, [lang]);

  useEffect(() => {
    fetchTemplates();
  }, [fetchTemplates, lang]);

  useEffect(() => {
    if (isDialogOpen) {
      attachDefaultFiles();
    }
  }, [isDialogOpen]);

  const attachDefaultFiles = async () => {
    await handleInsertPDF();
    await handleInsertImg();
    
    if (booking.passengers[0].payment_method === "BANK_TRANSFER") {
      await handleInsertInvoice();
    }
  };

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files) {
      setAttachments([...attachments, ...Array.from(event.target.files)]);
    }
  };

  const handleRemoveAttachment = (fileToRemove: File) => {
    setAttachments(attachments.filter((file) => file !== fileToRemove));
  };

  const onEditorReady = async (unlayer) => {
    if (!selectedTemplate) {
      console.warn("No selected template available.");
      return;
    }

    try {
      const response = await fetch(
        `/get-email-template?lang=${lang}&template_id=${selectedTemplate.id}&booking_id=${booking.id}` +
          `${selectedPassenger?.id ? `&passenger_id=${selectedPassenger.id}` : ""}`,
      );

      if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
      }

      const data = await response.json();

      if (data.design && typeof data.design === "object" && data.design.body && Array.isArray(data.design.body.rows)) {
        unlayer.loadDesign(data.design);
        unlayer.setBodyValues({
          contentWidth: "inherit",
        });
      } else {
        throw new Error("Invalid or incomplete design structure received.");
      }
    } catch (error) {
      showSnackbar("Error loading template content", "error");
      console.error("Detailed error:", error.message);
    }
  };

  const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
  };

  const handleInsertPDF = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`/generate-booking-pdf?booking_id=${booking.id}`);
      if (!response.ok) throw new Error("Failed to generate PDF");

      const blob = await response.blob();
      const file = new File([blob], `${booking.booking_code}.pdf`, { type: "application/pdf" });
      setPdfFile(file);
      //showSnackbar("Booking confirmation PDF previewed!", "success");
    } catch (error) {
      showSnackbar("Failed to generate PDF.", "error");
    } finally {
      setIsLoading(false);
    }
  };

  const handleInsertImg = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`/generate-img?booking_id=${booking.id}`);
      if (!response.ok) throw new Error("Failed to generate Img");

      const blob = await response.blob();
      const file = new File([blob], `BOOKED_${booking.id}.jpg`, { type: blob.type });
      setImgFile(file);
      //showSnackbar("Image previewed!", "success");
    } catch (error) {
      showSnackbar("Failed to generate image.", "error");
    } finally {
      setIsLoading(false);
    }
  };
  
  const handleInsertInvoice = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`/generate-invoice-pdf?booking_id=${booking.id}&lang=${lang}`);
      if (!response.ok) throw new Error("Failed to generate PDF");

      const blob = await response.blob();
      const file = new File([blob], `${booking.booking_code}inv.pdf`, { type: "application/pdf" });
      setInvoiceFile(file);
    } catch (error) {
      showSnackbar("Failed to generate Invoice.", "error");
    } finally {
      setIsLoading(false);
    }
  };

  const handlePreview = (file) => {
    const fileURL = URL.createObjectURL(file);
    window.open(fileURL, "_blank");
  };

  const handleSendEmail = () => {
    setIsConfirmDialogOpen(false);
    setIsSending(true);

    const unlayer = window.unlayer;
    if (!unlayer) return;

    unlayer.exportHtml(async (data) => {
      const { html } = data;
      const formData = new FormData();
      formData.append("lang", lang);
      formData.append("template_name", selectedTemplate.name);
      formData.append("email_content", html);
      formData.append("event_id", booking.event_id);
      formData.append("booking_id", booking.id);
      formData.append("template_id", selectedTemplate.id);
      formData.append("subject", subject);
      formData.append("selected_email", selectedPassenger.email);
      formData.append("selected_pass_id", selectedPassenger.id);
      attachments.forEach((file) => formData.append("attachments[]", file));
      formData.append("booking_pdf", pdfFile ? 1 : 0);
      formData.append("booking_image", imgFile ? 1 : 0);
      //formData.append("invoice_file", invoiceFile ? 1 : 0);
      if (invoiceFile) formData.append("attachments[]", invoiceFile);

      try {
        // for (let pair of formData.entries()) {
        //   console.log(`${pair[0]}:`, pair[1]);
        // }
        const response = await fetch("/send-email", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": getCsrfToken() },
          body: formData,
          credentials: "include",
        });

        const data = await response.json();
        if (data.success) {
          showSnackbar("Email sent successfully!", "success");
         // setAttachments([]);
        } else {
          showSnackbar("Failed to send email.", "error");
          throw new Error("Failed to send email.");
        }
      } catch (error) {
        showSnackbar("Error sending email, please try again.", "error");
        //alert("Error sending email, please try again.");
        console.error("Error sending email:", error);
      } finally {
        setIsSending(false);
      }
    });
  };

  const handleCloseDialog = () => {
    setIsDialogOpen(false);
    setAttachments([]);
  };

  const hasPDF = attachments.some((file) => file.name.endsWith(".pdf"));
  const hasImage = attachments.some((file) => file.type.startsWith("image/"));

  return (
    <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
      <Grid container spacing={2}>
        {/* Language Selector */}
        <Grid item xs={12} md={1}>
          <Select
              size="small"
              value={lang}
              onChange={(e) => handleLangChange(e.target.value)}
              fullWidth
              disabled={canSendEmail}
            >
              {LANGUAGES.map((language) => (
                <MenuItem key={language} value={language}>
                  {language.toUpperCase()}
                </MenuItem>
              ))}
          </Select>
        </Grid>

        {/* Template Selector */}
        <Grid item xs={12} md={5}>
          {isSending ? (
            <CircularProgress />
          ) : (
             <Autocomplete
                size="small"
                disabled={canSendEmail}
                options={templates}
                value={selectedTemplate}
                inputValue={templateInputValue}
                onInputChange={(e, newInput) => setTemplateInputValue(newInput)}
                onChange={(event, newValue) => {
                  setSelectedTemplate(newValue);
                  if (newValue) {
                    setSubject(newValue.subject + " " + booking.booking_code);
                    setTemplateInputValue(newValue.name);
                  } else {
                    setSubject("");
                  }
                }}
                getOptionLabel={(option) => option?.name || ""}
                isOptionEqualToValue={(option, value) => option?.id === value?.id}
                clearOnEscape
                renderOption={(props, option) => (
                  <li {...props} style={{ display: "flex", alignItems: "center" }}>
                    {option.priority === 1 && <span style={{ marginRight: 6 }}>⭐</span>}
                    {option.name}
                  </li>
                )}
                renderInput={(params) => <TextField {...params} label="Select an email template" fullWidth />}
              />

          )}
        </Grid>
        <Grid item xs={4}>
          {isSending ? (
            <CircularProgress />
          ) : (
            <Select
              size="small"
              disabled={canSendEmail}
              value={selectedPassenger ? JSON.stringify(selectedPassenger) : ""}
              onChange={(e) => {
                const selectedObject = JSON.parse(e.target.value);
                setSelectedPassenger({ id: selectedObject.id, email: selectedObject.email });
              }}
              displayEmpty
              fullWidth
            >
              <MenuItem value="" disabled>
                Select Passenger
              </MenuItem>
              {booking.passengers.map((passenger) => (
                <MenuItem key={passenger.id} value={JSON.stringify({ id: passenger.id, email: passenger.email })}>
                  {passenger.email}
                </MenuItem>
              ))}
            </Select>
          )}
        </Grid>

        {/* Button */}
        <Grid item xs={4}>
          <Button
            variant="outlined"
            color="primary"
            onClick={() => setIsDialogOpen(true)}
            fullWidth
            style={{ height: "40px" }}
            startIcon={<EditIcon />}
            disabled={canSendEmail || !selectedTemplate || !selectedPassenger}
          >
            Edit & Send
          </Button>
        </Grid>
      </Grid>

      {/* Email Editor Dialog */}
      <Dialog open={isDialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="lg" fullScreen={isFullscreen}>
        <DialogTitle>
          Edit Email Template
          <IconButton onClick={() => setIsFullscreen(!isFullscreen)} style={{ float: "right" }}>
            {isFullscreen ? <FullscreenExitIcon /> : <FullscreenIcon />}
          </IconButton>
        </DialogTitle>
        <DialogContent style={{ display: "flex", flexDirection: "column", height: "calc(100% - 60px)", padding: 0 }}>
          {isLoading && (
            <div
              style={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                backgroundColor: "rgba(0,0,0,0.5)",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                zIndex: 1000,
              }}
            >
              {/* <CircularProgress /> */}
            </div>
          )}
          <FormControl fullWidth style={{ paddingRight: "1rem" }}>
            <TextField
              label="Email Subject"
              variant="outlined"
              size="small"
              fullWidth
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              style={{ margin: "10px 10px 10px 10px" }}
            />
          </FormControl>
          <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <UnlayerEditor
              onReady={onEditorReady}
              options={{
                appearance: { theme: "dark" },
              }}
              style={{ width: "100%" }}
            />
          </div>

          <Paper
            elevation={3}
            style={{
              width: "100%",
              padding: "10px",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              backgroundColor: "#131313",
              zIndex: 10,
            }}
          >
            <div>
              {pdfFile && (
                <Chip
                  label={pdfFile.name}
                  onClick={() => handlePreview(pdfFile)}
                  onDelete={() => setPdfFile(null)}
                  style={{ marginRight: "5px" }}
                  icon={<PictureAsPdfIcon />}
                />
              )}
              {imgFile && (
                <Chip
                  label={imgFile.name}
                  onClick={() => handlePreview(imgFile)}
                  onDelete={() => setImgFile(null)}
                  style={{ marginRight: "5px" }}
                  icon={<InsertPhotoIcon />}
                />
              )}
              {invoiceFile && (
                <Chip
                  label={invoiceFile.name}
                  onClick={() => handlePreview(invoiceFile)}
                  onDelete={() => setInvoiceFile(null)}
                  style={{ marginRight: "5px" }}
                  icon={<ReceiptIcon />}
                />
              )}

              {attachments.map((file, index) => (
                <Chip
                  key={index}
                  label={file.name}
                  onDelete={() => handleRemoveAttachment(file)}
                  style={{ marginRight: "5px" }}
                  icon={
                    <IconButton onClick={() => handlePreview(file)} size="small">
                      <VisibilityIcon />
                    </IconButton>
                  }
                />
              ))}
            </div>

            <div>
              {!pdfFile && (
                <Tooltip title="Attach Booking Confirmation PDF">
                  <IconButton onClick={handleInsertPDF}>
                    <PictureAsPdfIcon />
                  </IconButton>
                </Tooltip>
              )}
              {!imgFile && (
                <Tooltip title="Attach Event Image">
                  <IconButton onClick={handleInsertImg}>
                    <InsertPhotoIcon />
                  </IconButton>
                </Tooltip>
              )}
              {!invoiceFile && (
                <Tooltip title="Attach Bank Transfer Invoice">
                  <IconButton onClick={handleInsertInvoice}>
                    <ReceiptIcon />
                  </IconButton>
                </Tooltip>
              )}
              
              <Tooltip title="Attach Other files">
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
            </div>
          </Paper>
        </DialogContent>

        <DialogActions>
          <Button onClick={handleCloseDialog} color="secondary">
            Cancel
          </Button>
          <Button
            onClick={() => setIsConfirmDialogOpen(true)}
            variant="contained"
            color="primary"
            disabled={isSending || isLoading}
          >
            {isSending ? "Sending..." : "Send Email"}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Confirm Send Email Dialog */}
      <Dialog open={isConfirmDialogOpen} onClose={() => setIsConfirmDialogOpen(false)}>
        <DialogTitle>Confirm Send Email</DialogTitle>
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
