import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import { Toolbar, Button } from "@mui/material";
import CreateCustomer from "./partials/CreateCustomer";

const Create = ({ auth, errors }: PageProps) => {
  const handleBack = () => {
    //window.history.back(); //Keeps ordering and filtering, does not reload when data changed on EDIT
    router.visit(route("customers.index"), {
      only: ["users"],
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Customers"}>
      <Head title="Create Customer" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleBack}>
          Back
        </Button>
      </Toolbar>
      <CreateCustomer />
    </AuthenticatedLayout>
  );
};

export default Create;
