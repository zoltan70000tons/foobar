import { useRef, useState, FormEventHandler } from "react";
import { useForm } from "@inertiajs/react";
import { TextField, Button, Dialog, DialogActions, DialogContent, DialogContentText, DialogTitle } from "@mui/material";

export default function DeleteUserForm() {
  const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
  const passwordInput = useRef<HTMLInputElement>(null);

  const {
    data,
    setData,
    delete: destroy,
    processing,
    reset,
    errors,
  } = useForm({
    password: "",
  });

  const confirmUserDeletion = () => {
    setConfirmingUserDeletion(true);
  };

  const deleteUser: FormEventHandler = (e) => {
    e.preventDefault();

    destroy(route("profile.destroy"), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.current?.focus(),
      onFinish: () => reset(),
    });
  };

  const closeModal = () => {
    setConfirmingUserDeletion(false);

    reset();
  };

  return (
    <section>
      <header>
        <h2>Delete Account</h2>

        <p>
          Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your
          account, please download any data or information that you wish to retain.
        </p>
      </header>

      <Button color="error" variant="contained" onClick={confirmUserDeletion}>
        Delete Account
      </Button>

      <Dialog open={confirmingUserDeletion} onClose={closeModal}>
        <form onSubmit={deleteUser}>
          <DialogTitle id="alert-dialog-title">Are you sure you want to delete your account?</DialogTitle>

          <DialogContent>
            <DialogContentText id="alert-dialog-description">
              Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your
              password to confirm you would like to permanently delete your account.
            </DialogContentText>

            <TextField
              required
              fullWidth
              ref={passwordInput}
              error={errors.password ? true : false}
              id="standard-password-input"
              label="Password"
              type="password"
              value={data.password}
              autoComplete="current-password"
              onChange={(e) => setData("password", e.target.value)}
            />
          </DialogContent>

          <DialogActions>
            <Button onClick={closeModal}>Cancel</Button>
            <Button color="error" variant="contained" disabled={processing} onClick={confirmUserDeletion}>
              Delete Account
            </Button>
          </DialogActions>
        </form>
      </Dialog>
    </section>
  );
}
