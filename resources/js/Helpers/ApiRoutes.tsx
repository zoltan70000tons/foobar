const API_BASE_URL = "http://localhost:8000";

const apiRoutes = {
  //Team 
  getTeamUrl: `${API_BASE_URL}/organization/getTeam`,
  sendInvitationsUrl: `${API_BASE_URL}/team/send-invitations`,

  //Roles
  orgRolesUrl: `${API_BASE_URL}/organization/roles`,
  assignRolesUrl: `${API_BASE_URL}/user/permissions`,
  rolesUrl: `${API_BASE_URL}/roles`,
  updateRole: `${API_BASE_URL}/organization/members/updateRole`,

  //permissions
  permissionUrl: `${API_BASE_URL}/organization/permissions`,
  addPermissionToRoleUrl: `${API_BASE_URL}/permissions/addToRole`,
  orgPermissionUrl: `${API_BASE_URL}/permissions`,
  getPermissions: `${API_BASE_URL}/users/getPermissions`,

  //cabins
  addCabinTags: (id: string) => `${API_BASE_URL}/events/${id}/cabins/addTag`,
  updateCabinStatus: (id: string) => `${API_BASE_URL}/events/${id}/cabins/updateStatus`,
};

export default apiRoutes;