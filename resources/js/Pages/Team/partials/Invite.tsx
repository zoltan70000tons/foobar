import React, { useState, useEffect } from "react";
import {
  TextField,
  Button,
  Box,
  Select,
  MenuItem,
  InputLabel,
  FormControl,
  Grid,
  CircularProgress,
  Alert,
  IconButton,
} from "@mui/material";
import { useForm } from "@inertiajs/react";
import axios from "axios";
import apiRoutes from "@/Helpers/ApiRoutes";
import { Add, Remove } from "@mui/icons-material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

export default function Invite() {
  const {
    data,
    setData,
    post,
    errors,
    clearErrors,
    setError: setFormError,
  } = useForm({
    invitations: [{ email: "", role: "" }],
  });

  const [roles, setRoles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const { showSnackbar } = useSnackbar();

  useEffect(() => {
    axios
      .get(apiRoutes.orgRolesUrl, { params: { org_id: "1" } })
      .then((response) => {
        setRoles(response.data.data);
      })
      .catch((error) => {
        setErrorMessage("Error fetching roles");
        console.error("Error fetching roles:", error);
      });
  }, []);

  const handleChange = (index, field, value) => {
    const newInvitations = [...data.invitations];
    newInvitations[index][field] = value;
    setData("invitations", newInvitations);
    clearErrors(`invitations.${index}.${field}`);
  };

  const handleAddInvitation = () => {
    setData("invitations", [...data.invitations, { email: "", role: "" }]);
  };

  const handleRemoveInvitation = (index) => {
    const newInvitations = data.invitations.filter((_, i) => i !== index);
    setData("invitations", newInvitations);
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    let isValid = true;
    data.invitations.forEach((invitation, index) => {
      if (!invitation.email) {
        setFormError(`invitations.${index}.email`, "Email is required");
        isValid = false;
      }
      if (!invitation.role) {
        setFormError(`invitations.${index}.role`, "Role is required");
        isValid = false;
      }
    });

    if (!isValid) return;
    setLoading(true);
    axios
      .post(apiRoutes.sendInvitationsUrl, data)
      .then((response) => {
        setLoading(false);
        showSnackbar(response.data.message, "success");
      })
      .catch((error) => {
        setLoading(false);
        showSnackbar("Some invitations could not be processed.", "error");
      });
  };

  return (
    <Box component="form" onSubmit={handleSubmit} sx={{ mt: 3 }}>
      {data.invitations.map((invitation, index) => (
        <Grid container spacing={2} alignItems="center" key={index}>
          <Grid item xs={4}>
            <TextField
              fullWidth
              size="small"
              label="Email"
              type="email"
              name={`invitations[${index}].email`}
              value={invitation.email}
              onChange={(e) => handleChange(index, "email", e.target.value)}
              error={!!errors[`invitations.${index}.email`]}
              helperText={errors[`invitations.${index}.email`]}
              margin="normal"
              required
            />
          </Grid>
          <Grid item xs={4}>
            <FormControl fullWidth margin="normal" required error={!!errors[`invitations.${index}.role`]}>
              <InputLabel id={`role-label-${index}`} style={{ top: -6 }}>
                Role
              </InputLabel>
              <Select
                size="small"
                fullWidth
                labelId={`role-label-${index}`}
                id={`role-${index}`}
                value={invitation.role}
                label="Role"
                onChange={(e) => handleChange(index, "role", e.target.value)}
              >
                <MenuItem value="">
                  <em>None</em>
                </MenuItem>
                {roles.map((role) => (
                  <MenuItem key={role.id} value={role.name}>
                    {role.name}
                  </MenuItem>
                ))}
              </Select>
              {errors[`invitations.${index}.role`] && <p>{errors[`invitations.${index}.role`]}</p>}
            </FormControl>
          </Grid>
          <Grid item xs={4}>
            <IconButton onClick={() => handleRemoveInvitation(index)} disabled={data.invitations.length === 1}>
              <Remove />
            </IconButton>
            {index === data.invitations.length - 1 && (
              <IconButton onClick={handleAddInvitation}>
                <Add />
              </IconButton>
            )}
          </Grid>
        </Grid>
      ))}
      <Box sx={{ mt: 3 }}>
        <Button
          type="submit"
          variant="outlined"
          color="primary"
          size="small"
          disabled={loading}
          sx={{ position: "relative" }}
        >
          {loading ? <CircularProgress size={24} /> : "Send Invitations"}
        </Button>
      </Box>
      {errorMessage && (
        <Alert severity="error" sx={{ mt: 2 }}>
          {errorMessage}
        </Alert>
      )}
    </Box>
  );
}
