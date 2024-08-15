import * as React from 'react';
import Link from '@mui/material/Link';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Title from './Title';

// Generate Order Data
function createData(
  id: number,
  date: string,
  name: string,
  cabin: string,
  paymentType: string,
  amount: number,
) {
  return { id, date, name, cabin, paymentType, amount };
}

const rows = [
  createData(
    0,
    '16 Mar, 2024',
    'Ray Gillen',
    'Private',
    'Full',
    2333,
  ),
  createData(
    1,
    '20 Mar, 2024',
    'Bruce Dickinson',
    'Private',
    'Full',
    2333,
  ),
  createData(2, '18 Mar, 2024', 'Ronnie James Dio', 'Private', 'Installments', 2333),
  createData(
    3,
    '16 Jul, 2024',
    'Ozzy Osbourne',
    'Private',
    'Installments',
    2333,
  ),
  createData(
    4,
    '13 Jun, 2024',
    'Corey Taylor',
    'Single Male',
    'Full',
    2199,
  ),
];

function preventDefault(event: React.MouseEvent) {
  event.preventDefault();
}

export default function RecentOrders() {
  return (
    <React.Fragment>
      <Title>Recent Orders</Title>
      <Table size="small">
        <TableHead>
          <TableRow>
            <TableCell>Date</TableCell>
            <TableCell>Name</TableCell>
            <TableCell>Cabin</TableCell>
            <TableCell>Payment Type</TableCell>
            <TableCell align="right">Sale Amount</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {rows.map((row) => (
            <TableRow key={row.id}>
              <TableCell>{row.date}</TableCell>
              <TableCell>{row.name}</TableCell>
              <TableCell>{row.cabin}</TableCell>
              <TableCell>{row.paymentType}</TableCell>
              <TableCell align="right">{`$${row.amount}`}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Link color="primary" href="#" onClick={preventDefault} sx={{ mt: 3 }}>
        See more orders
      </Link>
    </React.Fragment>
  );
}