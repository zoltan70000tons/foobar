import {
  Button,
} from "@mui/material";
import { usePage } from '@inertiajs/react';

//import { useTranslations } from "next-intl";

type SignInDialogProps = {
  isFull?: boolean;
  tAuth: any;
  language: 'en' | 'de' | 'es' | string;
};

export default function SignInDialog({ isFull }: SignInDialogProps) {

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
