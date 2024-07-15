
//TODO add this to .env
const API_BASE_URL = "http://localhost:8000/api";

const apiRoutes = {
  //Team 
  getTeamUrl: `${API_BASE_URL}/organization/getTeam`,

  //Roles
  orgRolesUrl: `${API_BASE_URL}/organization/roles`,
  assignRolesUrl: `${API_BASE_URL}/user/permissions`,
  rolesUrl: `${API_BASE_URL}/roles`,

  //permissions
  permissionUrl: `${API_BASE_URL}/organization/permissions`,
  addPermissionToRoleUrl: `${API_BASE_URL}/permissions/addToRole`,
  orgPermissionUrl: `${API_BASE_URL}/api/permissions`,

};

export default apiRoutes;
