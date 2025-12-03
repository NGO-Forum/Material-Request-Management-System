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

export interface menu {
  header?: string;
  title?: string;
  icon?: object;
  to?: string;
  divider?: boolean;
  chip?: string;
  chipColor?: string;
  chipVariant?: string;
  chipIcon?: string;
  children?: menu[];
  disabled?: boolean;
  type?: string;
  subCaption?: string;
}

const sidebarItem: menu[] = [
  { header: 'Dashboard' },
  {
    title: 'Default',
    icon: DashboardIcon,
    to: '/dashboard/default'
  },

  { divider: true },
  { header: 'Pages' },

  // Inventory Management
  {
    title: 'Inventory Management',
    icon: ClipboardTextIcon,
    to: '/inventory',
    children: [
      {
        title: 'List Material',
        icon: ListCheckIcon,
        to: '/inventory/list'
      },
      {
        title: 'Add Material',
        icon: UserPlusIcon,
        to: '/inventory/add'
      },
      {
        title: 'Inventory Reports',
        icon: ReportIcon,
        to: '/inventory/reports'
      }
    ]
  },

  // Material Request Management
  {
    title: 'Material Request Management',
    icon: FileTextIcon,
    to: '/requests',
    children: [
      {
        title: 'Create Request',
        icon: ClipboardTextIcon,
        to: '/requests/create'
      },
      {
        title: 'Request List',
        icon: ListCheckIcon,
        to: '/requests/list'
      },
      {
        title: 'Request Approval',
        icon: ShieldIcon,
        to: '/requests/approval'
      },
      {
        title: 'Request Tracking',
        icon: ReportIcon,
        to: '/requests/tracking'
      }
    ]
  },

  // Administration
  {
    title: 'Administration',
    icon: HelpIcon,
    to: '/admin',
    children: [
      {
        title: 'User',
        icon: UserIcon,
        to: '/admin/user'
      },
      {
        title: 'Role',
        icon: ShieldIcon,
        to: '/admin/role'
      },
      {
        title: 'Department',
        icon: BuildingIcon,
        to: '/admin/department'
      }
    ]
  },

  // Error
  {
    title: 'Error 404',
    icon: AlertTriangleIcon,
    to: '/error'
  }
];

export default sidebarItem;
