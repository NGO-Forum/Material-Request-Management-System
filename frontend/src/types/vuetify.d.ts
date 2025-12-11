export interface DataTableHeader {
  title: string;
  key: string;
  align?: 'start' | 'center' | 'end';
  width?: number | string;
  sortable?: boolean;
}
