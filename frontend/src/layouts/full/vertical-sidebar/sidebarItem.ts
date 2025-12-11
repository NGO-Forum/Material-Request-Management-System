// src/layouts/full/vertical-sidebar/sidebarItem.ts
import {
  DashboardIcon,
  ClipboardTextIcon,
  ListCheckIcon,
  ReportIcon,
  FileTextIcon,
  ShieldIcon,
  UserIcon,
  BuildingIcon,
  AlertTriangleIcon,
  HelpIcon,
  PlusIcon,
  PackageIcon,        
} from 'vue-tabler-icons';

export interface Menu {
  header?: string;
  title?: string;
  icon?: any;
  to?: string;
  divider?: boolean;
  chip?: string;
  chipColor?: string;
  chipVariant?: string;
  chipIcon?: string;
  children?: Menu[];
  disabled?: boolean;
  type?: string;
  subCaption?: string;
}

const sidebarItem: Menu[] = [
  { header: 'Dashboard' },
  {
    title: 'Default',
    icon: DashboardIcon,
    to: '/main/dashboard/default'
  },

  { divider: true },
  { header: 'Pages' },

  // Inventory Management
  {
    title: 'Inventory Management',
    icon: ClipboardTextIcon,
    children: [
      {
        title: 'List Categories',
        icon: ListCheckIcon,
        to: '/main/categories/list'
      },
      {
        title: 'List Materials',
        icon: PackageIcon,           
        to: '/main/materials/list'
      },
      {
        title: 'Inventory Reports',
        icon: ReportIcon,
        to: '/main/inventory/reports'
      }
    ]
  },

    // Material Request Management
    {
    title: 'Material Request Management',
    icon: FileTextIcon,
    children: [
      {
        title: 'Request List',
        icon: ListCheckIcon,
        to: '/main/requests/list'
      },
      {
        title: 'Create Request',
        icon: PlusIcon,
        to: '/main/requests/create'
      },
      {
        title: 'Request Tracking',
        icon: ReportIcon,
        to: '/main/requests/tracking'
      }
    ]
  },

  // Administration
  {
    title: 'Administration',
    icon: HelpIcon,
    children: [
      {
        title: 'Users',
        icon: UserIcon,
        to: '/main/users'
      },
      {
        title: 'Roles',
        icon: ShieldIcon,
        to: '/main/roles'
      },
      {
        title: 'Departments',
        icon: BuildingIcon,
        to: '/main/departments'
      }
    ]
  },
 
  {
    title: 'Error 404',
    icon: AlertTriangleIcon,
    to: '/main/error'
  }
];

export default sidebarItem;