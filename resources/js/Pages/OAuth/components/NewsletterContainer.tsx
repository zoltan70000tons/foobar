import { Button } from "@mui/material";
// import { useTranslations } from "next-intl";

export default function NewsletterContainer() {

  // const tFooter = useTranslations("Footer");
  const FRONTEND_URL = import.meta.env.VITE_FRONTEND_URL;

  return (
    <>
      <Button 
        href={`${FRONTEND_URL}?newsletter=true`}       
        type="button"
        variant="modern"
        color="secondary"
        sx={{ fontSize: {xs: "0.75em", sm: "1em", textAlign: "center"} }}
      >
        {/* {tFooter("newsletter")} */} Newsletter
      </Button>
    </>
  );
}
