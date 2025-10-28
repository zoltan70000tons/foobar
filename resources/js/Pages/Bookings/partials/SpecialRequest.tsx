import {
  Box,
  TextField,
  FormGroup,
  Checkbox,
  FormControlLabel,
  Typography,
  Select,
  MenuItem,
  InputLabel,
} from "@mui/material";
import { Passenger } from "@/Pages/Bookings/partials/Payment";
import React, { useEffect, useState } from "react";

export enum DietaryOptions {
  VEGETARIAN = "vegetarian",
  VEGAN = "vegan",
  GLUTEN_FREE = "gluten_free",
  NUT_ALLERGY = "nut_allergy",
  KOSHER = "kosher",
  HALAL = "halal",
  LACTOSE_INTOLERANT = "lactose_intolerant",
  DIABETIC = "diabetic",
}

export const DietaryOptionsLabels: Record<DietaryOptions, string> = {
  [DietaryOptions.VEGETARIAN]: "Vegetarian",
  [DietaryOptions.VEGAN]: "Vegan",
  [DietaryOptions.GLUTEN_FREE]: "Gluten Free",
  [DietaryOptions.NUT_ALLERGY]: "Nut Allergy",
  [DietaryOptions.KOSHER]: "Kosher",
  [DietaryOptions.HALAL]: "Halal",
  [DietaryOptions.LACTOSE_INTOLERANT]: "Lactose Intolerant",
  [DietaryOptions.DIABETIC]: "Diabetic",
};

type ContactDetailsProps = {
  passenger: Passenger;
  onChange: (string, value) => void;
  disabledByDesign: boolean;
};

export default function SpecialRequest({
  passenger,
  onChange,
  disabledByDesign,
}: ContactDetailsProps) {
  const options = [
    { label: "Wheelchair", value: "wheelchair_assistance" },
    {
      label: "Dietary Restrictions",
      value: "dietary_restrictions",
    },
    { label: "Other", value: "other" },
  ];

  const [specialOptions, setSpecialOptions] = useState({
    other: false,
    dietary_restrictions: false,
    wheelchair_assistance: false,
  });

  // Sync with passenger.special_options
  useEffect(() => {
    if (passenger?.special_options) {
      setSpecialOptions({
        other: !!passenger.special_options.other,
        dietary_restrictions: !!passenger.special_options.dietary_restrictions,
        wheelchair_assistance: !!passenger.special_options.wheelchair_assistance,
      });
    }
  }, [passenger]);

  const handleCheckboxChange = (key: keyof typeof specialOptions) => (
    e: React.ChangeEvent<HTMLInputElement>
  ) => {
    const updatedValue = e.target.checked;
    const updatedOptions = {
      ...specialOptions,
      [key]: updatedValue,
    };

    setSpecialOptions(updatedOptions);
    onChange("special_options", updatedOptions);
  };

  const otherSelected = specialOptions.other;
  const dietarySelected = specialOptions.dietary_restrictions;

  console.log("Other Selected:", otherSelected);
  console.log("Dietary Restrictions Selected:", dietarySelected);

  let dietarySelect = <></>;
  if (dietarySelected) {
    dietarySelect = <>
      <InputLabel id="info-select-label">Please select dietary preferences</InputLabel>
      <Select
        labelId="dietary-options-select-label"
        id="dietaryOptions"
        name="dietaryOptions"
        label="Dietary Options"
        autoWidth={true}
        multiple={true}
        value={
          Array.isArray(passenger.dietary_preferences)
            ? passenger.dietary_preferences
            : typeof passenger.dietary_preferences === "string"
              ? (() => {
                try {
                  const parsed = JSON.parse(passenger.dietary_preferences);
                  return Array.isArray(parsed) ? parsed : [];
                } catch {
                  return [];
                }
              })()
              : []
        }
        onChange={(e) => onChange("dietary_preferences", e.target.value)}
        sx={{
          backgroundColor: "rgba(255, 255, 255, 0.05)",
        }}
        fullWidth
        MenuProps={{
          PaperProps: {
            sx: {
              width: '100%',
            },
          },
        }}
      >
        {Object.values(DietaryOptions).map((option) => (
          <MenuItem key={option} value={option}>
            {DietaryOptionsLabels[option]}
          </MenuItem>
        ))}
      </Select>
    </>
  }

  return (
    <Box sx={{ mt: 2, mb: 4 }}>
      <FormGroup sx={{ mb: 2 }}>
        {options.map((option) => {
          return (
            <div key={option.value}>
              <FormControlLabel
                control={
                  <Checkbox
                    checked={specialOptions[option.value as keyof typeof specialOptions]}
                    onChange={handleCheckboxChange(option.value as keyof typeof specialOptions)}
                  />
                }
                label={option.label}
              />
              {option.value === "dietary_restrictions" ? dietarySelect : null}
            </div>
          );
        })}
      </FormGroup>

      {otherSelected && (
        <>
          <Typography variant="body2" sx={{ mb: 2 }}>
            Additional info
          </Typography>
          <TextField
            label="Special Request"
            variant="outlined"
            fullWidth
            multiline
            placeholder="e.g. Allergy to peanuts, prefer cabin near elevator"
            rows={3}
            size="small"
            value={passenger?.special_request || ""}
            onChange={(e) => onChange("special_request", e.target.value)}
            disabled={disabledByDesign}
          />
        </>
      )}
    </Box>
  );
}
