import React, { useEffect, useMemo, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import {
  Container,
  Grid,
  Toolbar,
  Box, Button,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import "dayjs/locale/en";
import { Permissions } from "@/enums/PermissionEnum";
import MuiTable from "@/Components/tables/MuiTable";
import LoadingOverlay from "@/Components/LoadingOverlay";
import { Visibility } from "@mui/icons-material";

const Index = ({ auth, customers }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { get } = useForm();

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (customers) {
      setLoading(false);
    }
  }, [customers]);

  const columns = useMemo(
    () => [
      {
        header: "eMail",
        accessor: "email",
        filterable: true,
        sortable: true,
        width: "26%",
      },
      {
        accessor: "first_name",
        header: "First Name",
        filterable: true,
        sortable: true,
        width: "17%",
      },
      {
        accessor: "last_name",
        header: "Last Name",
        filterable: true,
        sortable: true,
        width: "17%",
      },
      {
        accessor: "dob",
        header: "Date of Birth",
        filterable: true,
        sortable: true,
        width: "17%",
      },
      {
        accessor: "survivor_number",
        header: "Survivor Number",
        filterable: true,
        sortable: true,
        width: "17%",
      },
      {
        accessor: "membership_type",
        header: "Membership",
        filterable: true,
        sortable: true,
        width: "13%",
      },
      {
        header: "Actions",
        accessor: "id",
        disableFilter: true,
        width: "13%",
        draw: (row) => (
          <div style={ { display: "flex", gap: "10px" } }>
            { hasPermission(Permissions.ViewCustomers) && (
              <Visibility
                onClick={ () => {
                  router.get(
                    route("customers.show", { customer: row.id })
                  );
                } }
                style={ { cursor: "pointer" } }
              />
            ) }
          </div>
        ),
      },
    ],
    []
  );

  const handleCreate = () => {
    get(route('customers.create', {}));
  }

  return (
    <AuthenticatedLayout user={ auth.user } header={ "Customers" }>
      <Head title="Customers"/>
      <Toolbar sx={ { mt: 8 } }>
        <Button variant="outlined" color="secondary" onClick={ handleCreate }>
          New Customer
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={ { mb: 4 } }>
        <Grid container spacing={ 3 }>
          <Grid item xs={ 12 }>
            <Box>
              <Box>
                { customers ? (<MuiTable
                  columns={ columns }
                  data={ customers }
                  showCheckBox={false}
                />) : <></> }
              </Box>
            </Box>
            <LoadingOverlay open={ loading }/>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
