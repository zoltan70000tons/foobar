import { useState } from "react";
import { Container, Grid, TextareaAutosize, Paper, Typography } from "@mui/material";

const HtmlEditor = () => {
  const [html, setHtml] = useState<string>("<h1>Hello, World!</h1>");

  return (
    <Container maxWidth="md" sx={{ mt: 4 }}>
      <Typography variant="h4" gutterBottom>
        HTML Editor & Previewer
      </Typography>

      <Grid container spacing={3}>
        {/* Editor */}
        <Grid item xs={12} md={6}>
          <Paper elevation={3} sx={{ p: 2 }}>
            <Typography variant="h6">Editor</Typography>
            <TextareaAutosize
              minRows={10}
              value={html}
              onChange={(e) => setHtml(e.target.value)}
              style={{
                width: "100%",
                fontFamily: "monospace",
                fontSize: "16px",
                padding: "8px",
                border: "1px solid #ccc",
                borderRadius: "4px",
              }}
            />
          </Paper>
        </Grid>

        {/* Previsualización */}
        <Grid item xs={12} md={6}>
          <Paper elevation={3} sx={{ p: 2 }}>
            <Typography variant="h6">Preview</Typography>
            <div
              style={{
                minHeight: "200px",
                padding: "8px",
                border: "1px solid #ccc",
                borderRadius: "4px",
              }}
              dangerouslySetInnerHTML={{ __html: html }}
            />
          </Paper>
        </Grid>
      </Grid>
    </Container>
  );
};

export default HtmlEditor;
