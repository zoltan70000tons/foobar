import React, { useEffect, useRef } from "react";

const UnlayerEditor = ({ onReady, options, style }) => {
  const editorRef = useRef(null);

  useEffect(() => {
    if (window.unlayer) {
      window.unlayer.init({
        id: "unlayer-editor",
        ...options,
      });

      window.unlayer.addEventListener("editor:ready", () => {
        if (onReady) onReady(window.unlayer);
      });
    }

    return () => window.unlayer.destroy();
  }, []);

  const exportHtml = () => {
    window.unlayer.exportHtml((data) => {
      const { html } = data;
      console.log("Exported HTML:", html);
    });
  };

  return (
    <>
      <div id="unlayer-editor" style={{ height: "600px", width: "100%", ...style }} ref={editorRef}></div>
      {/* <button onClick={exportHtml} style={{ marginTop: "10px" }}>Export HTML</button> */}
    </>
  );
};

export default UnlayerEditor;
