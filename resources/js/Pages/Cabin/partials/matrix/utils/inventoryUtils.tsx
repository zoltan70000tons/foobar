import { Box } from "@mui/material";
import { InventoryStatus } from "@/types/cabin";

export function renderInventoryRow(label: string, color: string, value: number) {
  return (
    <Box sx={{ color, textAlign: "center" }}>
      {label}: {value}
    </Box>
  );
}

export function renderInventory(inventory: InventoryStatus) {
  return (
    <>
      {renderInventoryRow("Available", "green", inventory.AVAILABLE)}
      {renderInventoryRow("Internally Available", "yellow", inventory.PARTIALLY_BOOKED)}
      {renderInventoryRow("In progress", "red", inventory.IP)}
    </>
  );
}
