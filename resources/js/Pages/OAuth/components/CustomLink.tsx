import React, { useEffect, useState } from "react";
import { Link, Button, IconButton } from "@mui/material";
import { red } from "@mui/material/colors";

interface CustomLinkProps {
  type?: "button" | "link" | "iconButton" | "routerLink";
  href?: string;
  children: React.ReactNode;
  variant?: "modern" | "text" | "contained";
  [key: string]: any;
}

const CustomLink = ({ type = "link", href, children, variant = "text", ...props }: CustomLinkProps) => {
  const [isExternal, setIsExternal] = useState<boolean>(false);

  useEffect(() => {
    if (
      href &&
      !href.startsWith(window.location.origin) &&
      !href.startsWith("javascript:") &&
      !href.startsWith("mailto:") &&
      !href.startsWith("tel:")
    ) {
      setIsExternal(true);
    }
  }, [href]);

  const commonProps = {
    href,
    target: isExternal ? "_blank" : "_self",
    rel: isExternal ? "noreferrer" : undefined,
    ...props,
  };

  if (type === "button") {
    return (
      <Button {...commonProps} variant={variant} sx={{ color: "inherit", margin: 0 }}>
        {children}
      </Button>
    );
  }

  if (type === "iconButton") {
    return <IconButton {...commonProps}>{children}</IconButton>;
  }

  return (
    <Link
      sx={{
        color: "#fff",
        textDecoration: "none",
        cursor: "pointer",
        "&:hover": {
          color: red[500],
        },
        transition: "color ease 0.3s",
      }}
      {...commonProps}
    >
      {children}
    </Link>
  );
};

export default CustomLink;
