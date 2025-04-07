import { useCallback, useEffect, useState } from "react";

// Define or import the ColumnProps type
type ColumnProps<T> = {
  accessor: keyof T | string;
  Header?: string;
};


export function useServerPagination<T>({
    fetchData,
    columns,
    rowsPerPage,
    serverSidePagination,
  }: {
    fetchData?: (
      page: number,
      rowsPerPage: number,
      filters: { [key: string]: string },
      sort: { key: keyof T | string; direction: "asc" | "desc" }
    ) => Promise<{ data: T[]; total: number }>;
    columns: ColumnProps<T>[];
    rowsPerPage: number;
    serverSidePagination: boolean;
  }) {
    const [page, setPage] = useState(0);
    const [sort, setSort] = useState({
      key: columns[0]?.accessor ?? "",
      direction: "asc" as const,
    });
    const [filters, setFilters] = useState<{ [key: string]: string }>({});
    const [loading, setLoading] = useState(false);
    const [data, setData] = useState<T[]>([]);
    const [total, setTotal] = useState(0);
  
    const loadData = useCallback(async () => {
      if (!serverSidePagination || !fetchData || !sort.key) return;
  
      setLoading(true);
      try {
        const result = await fetchData(page, rowsPerPage, filters, sort);
        setData(result.data);
        setTotal(result.total);
      } catch (e) {
        console.error("Fetch error:", e);
      } finally {
        setLoading(false);
      }
    }, [fetchData, page, rowsPerPage, filters, sort, serverSidePagination]);
  
    useEffect(() => {
      loadData();
    }, [loadData]);
  
    return {
      page,
      setPage,
      sort,
      setSort,
      filters,
      setFilters,
      loading,
      data,
      total,
    };
  }
