import {
  Button,
} from "@mui/material";
import { usePage } from '@inertiajs/react';

//import { useTranslations } from "next-intl";

type SignInDialogProps = {
  tAuth: {
    signIn: string;
  }
};

export default function SignInDialog({ isFull }: { isFull: boolean }) {

  const { tAuth } = usePage<SignInDialogProps>().props;

  return (
    <Button 
      variant="modern" 
      color="primary"
      fullWidth={isFull}
    >
      {tAuth?.signIn ?? 'Sign In'}
    </Button>
  );
}
