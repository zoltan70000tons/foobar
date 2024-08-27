import React from "react";
import { EventStatus, EventStatusLabels } from "@/enums/EventStatusEnum";
import { MenuItem, Select, SelectChangeEvent, FormControl, InputLabel } from "@mui/material";

const EventStatusSelect = ({
  value,
  onChange,
  errors
}: {
  value: EventStatus;
  onChange: (event: SelectChangeEvent<EventStatus>) => void;
  errors: object
}) => {
  return (
    <FormControl fullWidth>
      <InputLabel id="event-status-label">Event Status</InputLabel>
      <Select
        labelId="event-status-label"
        value={value}
        onChange={onChange}
        displayEmpty
        name="status"
        label="Event Status"
        error={Boolean(errors.status)}
        helperText={errors.status}
      >
        {Object.entries(EventStatusLabels).map(([status, label]) => (
          <MenuItem key={status} value={status}>
            {label}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  );
};

export default EventStatusSelect;

