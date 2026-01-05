import React from "react";
import Backdrop from "@mui/material/Backdrop";
import CircularProgress from "@mui/material/CircularProgress";
import { styled } from "@mui/system";

const StyledBackdrop = styled(Backdrop)(({ theme }) => ({
  zIndex: theme?.zIndex?.drawer + 1,
  color: "#fff",
}));

const LoadingOverlay = ({ open }) => {
  return (
    <StyledBackdrop open={open}>
      <CircularProgress color="inherit" />
    </StyledBackdrop>
  );
};

export default LoadingOverlay;
