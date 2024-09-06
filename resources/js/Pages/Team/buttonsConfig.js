import { Permissions } from "@/enums/PermissionEnum";

const buttonsConfig = [
    { href: '/team/', label: 'Team', requiredPermission: Permissions.ViewUsers},
    { href: '/team/roles', label: 'Roles', requiredPermission: Permissions.ViewRoles },
    { href: '/team/permissions', label: 'Permissions', requiredPermission: Permissions.ViewPermissions },
];

export default buttonsConfig;
