import { Skeleton, TableCell } from "@mui/material";

type CellValueProps = {
  loading: boolean;
  children?: React.ReactNode;
  width?: number | string;
};

const CellValue = ({ loading, children, width = 120 }: CellValueProps) => {
  return (
    <TableCell>
      {loading ? <Skeleton width={width} /> : children}
    </TableCell>
  );
};

export default CellValue;
