import {
  Button,
} from "@mui/material";

//import { useTranslations } from "next-intl";

type SignInDialogProps = {
  isFull?: boolean;
};

export default function SignInDialog({ isFull }: SignInDialogProps) {

  //const tNavigation = useTranslations("Navigation");

  return (
    <Button 
      variant="modern" 
      color="primary"
      fullWidth={isFull}
    >
      {/* {tNavigation("signIn")} */} Sign In
    </Button>
  );
}
