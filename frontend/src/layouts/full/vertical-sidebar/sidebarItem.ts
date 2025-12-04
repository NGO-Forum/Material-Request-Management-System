// src/layouts/vertical-sidebar/sidebarItem.ts
import {
  DashboardIcon,
  KeyIcon,
  LoginIcon,
  UserPlusIcon,
  UserIcon,
  ShieldIcon,
  BuildingIcon,
  ListCheckIcon,
  ClipboardTextIcon,
  ReportIcon,
  AlertTriangleIcon,
  FileTextIcon,
  HelpIcon
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
    to: '/main/dashboard/default'  // Fixed: full path under /main
  },

  { divider: true },
  { header: 'Pages' },

  // Inventory Management
  {
    title: 'Inventory Management',
    icon: ClipboardTextIcon,
    children: [
      {
        title: 'List Material',
        icon: ListCheckIcon,
        to: '/main/inventory/list'
      },
      {
        title: 'Add Material',
        icon: UserPlusIcon,
        to: '/main/inventory/add'
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
        title: 'Create Request',
        icon: ClipboardTextIcon,
        to: '/main/requests/create'
      },
      {
        title: 'Request List',
        icon: ListCheckIcon,
        to: '/main/requests/list'
      },
      {
        title: 'Request Approval',
        icon: ShieldIcon,
        to: '/main/requests/approval'
      },
      {
        title: 'Request Tracking',
        icon: ReportIcon,
        to: '/main/requests/tracking'
      }
    ]
  },

  // Administration - FIXED PATH
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
    to: '/error'
  }
];

export default sidebarItem;