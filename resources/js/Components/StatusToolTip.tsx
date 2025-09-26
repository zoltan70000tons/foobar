import Tooltip, { TooltipProps } from "@mui/material/Tooltip";
import Chip, { ChipProps } from "@mui/material/Chip";
import Stack from "@mui/material/Stack";
import Typography from "@mui/material/Typography";
import { SxProps, Theme } from "@mui/material/styles";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import HourglassBottomIcon from "@mui/icons-material/HourglassBottom";
import ShoppingCartIcon from "@mui/icons-material/ShoppingCart";
import DonutLargeIcon from "@mui/icons-material/DonutLarge";
import BlockIcon from "@mui/icons-material/Block";
import { CabinStatus } from "@/enums/CabinStatus";

type StatusMeta = {
  label: string;
  description: string;
  color: ChipProps["color"];
  icon: React.ReactElement;
};

export const STATUS_META: Record<CabinStatus, StatusMeta> = {
  AVAILABLE: {
    label: "Publicly Available",
    description: "This cabin is open for immediate booking and visible to both customers and agents.",
    color: "success",
    icon: <CheckCircleIcon fontSize="small" />,
  },
  RESERVED: {
    label: "Internally Available",
    description:
      "Only agents can use this cabin through the admin panel. It is hidden from public purchase. Usually means it's reserved for a specific purpose or got returned from a cancellation or booking modification.",
    color: "info",
    icon: <HourglassBottomIcon fontSize="small" />,
  },
  BOOKED: {
    label: "Booked",
    description:
      "This cabin is linked to an active booking and is no longer available and there are not any available spots.",
    color: "primary",
    icon: <ShoppingCartIcon fontSize="small" />,
  },
  PARTIALLY_BOOKED: {
    label: "Partially Booked",
    description: "This cabin is linked to one or more Single Ticket bookings and still has some available space.",
    color: "warning",
    icon: <DonutLargeIcon fontSize="small" />,
  },
  CLOSED: {
    label: "Closed",
    description: "This cabin is not available for booking due to administrative or operational reasons.",
    color: "error",
    icon: <BlockIcon fontSize="small" />,
  },
};

export function getStatusDescription(status: CabinStatus): string {
  return STATUS_META[status].description;
}

export function getStatusLabel(status: CabinStatus): string {
  return STATUS_META[status].label;
}

/**
 * A lightweight tooltip wrapper that always shows the fixed description for the given status.
 * Use it around any element (icons, text, buttons, etc.).
 */
export function StatusTooltip({
  status,
  title,
  children,
  ...props
}: {
  status: CabinStatus;
  title?: React.ReactNode;
  children: React.ReactElement;
} & Omit<TooltipProps, "title" | "children">) {
  const meta = STATUS_META[status];

  const defaultTitle = (
    <Stack spacing={0.5}>
      <Typography variant="subtitle2" fontWeight={600} sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
        {meta.icon} {meta.label}
      </Typography>
      <Typography variant="body2">{meta.description}</Typography>
    </Stack>
  );

  return (
    <Tooltip arrow enterDelay={300} title={title ?? defaultTitle} {...props} placement="right">
      {children}
    </Tooltip>
  );
}

/**
 * A ready-to-use Chip that shows the status label, color and icon,
 * with the reusable tooltip baked in.
 */
export function StatusChip({
  status,
  sx,
  ...chipProps
}: {
  status: CabinStatus;
  sx?: SxProps<Theme>;
} & Omit<ChipProps, "label" | "color" | "icon">) {
  const meta = STATUS_META[status];

  return (
    <StatusTooltip status={status} placement="right">
      <Chip
        icon={meta.icon}
        label={meta.label}
        color={meta.color}
        variant="filled"
        size="small"
        sx={{ fontWeight: 600, ...(sx as object) }}
        {...chipProps}
      />
    </StatusTooltip>
  );
}
