import React, { useMemo, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Grid, Toolbar, Box, Button, Autocomplete, TextField } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import "dayjs/locale/en";
import { Permissions } from "@/enums/PermissionEnum";
import MuiTable from "@/Components/tables/MuiTable";
import { Visibility, Check, Close, Beenhere } from "@mui/icons-material";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import type { AxiosResponse } from "axios";
import NewBookingModal from "@/Pages/Bookings/NewBookingModal";

enum PotentialSurvivorMatchesTypes {
  "match" = "Match",
  "double_booking" = "Double Booking",
}
type PotentialSurvivorMatchesTypesKeys = "match" | "double_booking";

export enum PotentialSurvivorMatchStatus {
  InProgress = "in_progress",
  Reviewed = "reviewed",
  Approved = "approved",
  Rejected = "rejected",
  Resolved = "resolved",
}

export const PotentialSurvivorMatchStatusLabel: Record<PotentialSurvivorMatchStatus, string> = {
  [PotentialSurvivorMatchStatus.InProgress]: "In Progress",
  [PotentialSurvivorMatchStatus.Reviewed]: "Reviewed",
  [PotentialSurvivorMatchStatus.Approved]: "Approved",
  [PotentialSurvivorMatchStatus.Rejected]: "Rejected",
  [PotentialSurvivorMatchStatus.Resolved]: "Resolved",
};

export type PotentialSurvivorMatches = {
  created_at: string;
  id: number;
  passenger_dob: string;
  passenger_first_name: string;
  passenger_last_name: string;
  passenger_id: number;
  review_date: string | null;
  reviewer_id: string | null;
  score: number;
  status: PotentialSurvivorMatchStatus;
  type: PotentialSurvivorMatchesTypesKeys;
  updated_at: string;
  user_detail_id: number;
  user_dob: string;
  user_first_name: string;
  user_last_name: string;
  data: any;
};

type Props = PageProps & {
  auth: AuthProps;
  potentialMatches: PotentialSurvivorMatches;
  statuses: PotentialSurvivorMatchStatus[];
};

const Index = ({ auth, potentialMatches, statuses }: Props) => {
  const { hasPermission } = usePermissions();
  const { showSnackbar } = useSnackbar();

  const [selectedStatuses, setSelectedStatuses] = useState([PotentialSurvivorMatchStatus.InProgress]);

  const columns = useMemo(
    () => [
      {
        header: "Score",
        accessor: "score",
        filterable: false,
        sortable: true,
        width: "6%",
        draw: (row: PotentialSurvivorMatches) => <>{row.score.toFixed(2)}</>,
      },
      {
        header: "Type",
        accessor: "type",
        filterable: true,
        sortable: true,
        width: "8%",
        draw: (row: PotentialSurvivorMatches) => <>{PotentialSurvivorMatchesTypes[row.type]}</>,
      },
      {
        header: "Pax Name",
        accessor: "passenger_name",
        filterable: true,
        sortable: true,
        width: "15%",
      },
      {
        accessor: "passenger_dob",
        header: "Pax DOB",
        filterable: true,
        sortable: true,
        width: "15%",
      },
      {
        header: "User Name",
        accessor: "user_name",
        filterable: true,
        sortable: true,
        width: "15%",
      },
      {
        accessor: "user_dob",
        header: "User DOB",
        filterable: true,
        sortable: true,
        width: "15%",
      },
      {
        header: "Status",
        accessor: "status",
        filterable: true,
        sortable: true,
        width: "14%",
        draw: (row: PotentialSurvivorMatches) => <>{PotentialSurvivorMatchStatusLabel[row.status]}</>,
      },
      {
        header: "Actions",
        accessor: "id",
        disableFilter: true,
        width: "12%",
        draw: (row: PotentialSurvivorMatches) => (
          <>
            {row.status === PotentialSurvivorMatchStatus.InProgress && (
              <Box sx={{ display: "flex", gap: "4px" }}>
                <div style={{ display: "flex", gap: "10px" }}>
                  {hasPermission(Permissions.ViewCustomers) && (
                    <Visibility
                      onClick={() => {
                        router.get(route("matches.show", { id: row.id }));
                      }}
                      style={{ cursor: "pointer" }}
                    />
                  )}
                </div>
                {row.type === "match" && (
                  <>
                    <div>
                      {hasPermission(Permissions.EditCustomers) && (
                        <Check
                          color="success"
                          onClick={() => {
                            router.put(route("matches.update", { id: row.id }));
                          }}
                          style={{ cursor: "pointer" }}
                        />
                      )}
                    </div>
                    <div>
                      {hasPermission(Permissions.DeleteCustomers) && (
                        <Close
                          color="error"
                          onClick={() => {
                            router.delete(route("matches.destroy", { id: row.id }));
                          }}
                          style={{ cursor: "pointer" }}
                        />
                      )}
                    </div>
                  </>
                )}
                {row.type === "double_booking" && (
                  <div>
                    {hasPermission(Permissions.EditCustomers) && (
                      <Beenhere
                        color="success"
                        onClick={() => {
                          router.put(route("matches.resolve", { id: row.id }));
                        }}
                        style={{ cursor: "pointer" }}
                      />
                    )}
                  </div>
                )}
              </Box>
            )}
            {row.status !== PotentialSurvivorMatchStatus.InProgress && (
              <Box sx={{ display: "flex", gap: "4px" }}>
                <div style={{ display: "flex", gap: "10px" }}>
                  {hasPermission(Permissions.ViewCustomers) && (
                    <Visibility
                      onClick={() => {
                        router.get(route("matches.show", { id: row.id }));
                      }}
                      style={{ cursor: "pointer" }}
                    />
                  )}
                </div>
              </Box>
            )}
          </>
        ),
      },
    ],
    [],
  );

  const fetchPotentialMatches = async (
    page: number,
    rowsPerPage: number,
    filters: { [key: string]: string },
    sort: { key: string; direction: "asc" | "desc" },
  ): Promise<{ data: PotentialSurvivorMatches[]; total: number }> => {
    try {
      const response = await axios.get("/potential-survivor-matches/paginated", {
        params: {
          page,
          per_page: rowsPerPage,
          sort_by: sort.key,
          sort_direction: sort.direction,
          filters: JSON.stringify(filters),
          statuses: selectedStatuses,
        },
        paramsSerializer: (params: Record<string, string>) => {
          return new URLSearchParams(params as Record<string, string>).toString();
        },
      });

      return {
        data: response.data?.data ?? [],
        total: response.data?.total ?? 0,
      };
    } catch (error) {
      console.error("Error fetching customers:", error);
      return { data: [], total: 0 };
    }
  };

  const handleManualSync = async () => {
    showSnackbar("Sync started successfully", "success");

    await axios
      .post<{ status: number; output: string }>(route("matches.run-manual-sync"))
      .then((res: AxiosResponse<{ status: number; output: string }>) => {
        if (res?.data?.status === 0) {
          showSnackbar(res.data.output, "success");
        } else {
          showSnackbar("Something went wrong", "error");
        }
      })
      .catch(() => showSnackbar("Something went wrong", "error"));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Potential Survivor Matches"}>
      <Head title="Potential Survivor Matches" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleManualSync} sx={{ mr: 2 }}>
          Run manual sync
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Grid item xs={12}>
              <Box
                sx={{
                  display: "flex",
                  justifyContent: "flex-end",
                  alignItems: "stretch",
                  gap: 2,
                }}
              >
                <Autocomplete
                  multiple
                  size="small"
                  options={statuses}
                  getOptionLabel={(option) => PotentialSurvivorMatchStatusLabel[option]}
                  value={selectedStatuses}
                  onChange={(event, newValue) => setSelectedStatuses(newValue)}
                  renderInput={(params) => <TextField {...params} variant="outlined" placeholder="Filter by Status" />}
                  sx={{ minWidth: 250 }}
                />
              </Box>
            </Grid>
            <Box>
              <Box>
                {potentialMatches ? (
                  <MuiTable
                    columns={columns}
                    data={potentialMatches?.data}
                    showCheckBox={false}
                    serverSidePagination={true}
                    fetchData={fetchPotentialMatches}
                  />
                ) : (
                  <></>
                )}
              </Box>
            </Box>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
