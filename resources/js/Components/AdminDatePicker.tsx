import * as React from 'react';
import { DemoContainer, DemoItem } from '@mui/x-date-pickers/internals/demo';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { DatePicker} from '@mui/x-date-pickers/DatePicker';
import { DateRangePicker } from '@mui/x-date-pickers-pro/DateRangePicker';

export default function AdminDatePicker({onChange, label, name, disablePast=true}) {
  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <DemoContainer components={['DateRangePicker']}>
      <DemoItem label="DateRangePicker" component="DateRangePicker">
          <DateRangePicker  disablePast />
        </DemoItem>
        {/* <DatePicker label={label} 
            name={name}
            onChange={onChange}
            disablePast={disablePast}
        /> */}
      </DemoContainer>
    </LocalizationProvider>
  );
}