import DatePicker from "react-datepicker";
import React, { useState } from "react";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import "react-datepicker/dist/react-datepicker.css";
import DateRangeIcon from '@mui/icons-material/DateRange';

export type DateRange = {
  startDate: Date | null;
  endDate: Date | null;
};

type HeaderDateRangeProps = {
  accessor: string;
  dateRangeState: Record<string, DateRange>;
  setDateRangeState: React.Dispatch<React.SetStateAction<Record<string, DateRange>>>;
};

export const HeaderDateRange = ({ dateRangeState, accessor, setDateRangeState }: HeaderDateRangeProps) => {
  const [isDatePickerVisible, setDatePickerVisible] = useState(false);

  const handleIconClick = () => {
    setDatePickerVisible((prevState) => !prevState);
  };

  const handleDateChange = (dates, columnAccessor) => {
    const [start, end] = dates;

    setDateRangeState((prevState) => ({
      ...prevState,
      [columnAccessor]: {
        startDate: start,
        endDate: end,
      }
    }));
  };

  return (
    <>
      <DateRangeIcon onClick={handleIconClick} sx={{cursor: 'pointer'}} />
      {isDatePickerVisible && (
        <LocalizationProvider dateAdapter={AdapterDayjs}>
          <DatePicker
            selected={dateRangeState[accessor]?.startDate || null}
            onChange={(dates) => handleDateChange(dates, accessor)}
            name={accessor as string}
            selectsRange
            startDate={dateRangeState[accessor]?.startDate || null}
            endDate={dateRangeState[accessor]?.endDate || null}
            placeholderText="Select a date range"
            isClearable
            inline={false}
            dateFormat="yyyy-MM-dd"
          />
        </LocalizationProvider>
      )}
    </>
  );
}
