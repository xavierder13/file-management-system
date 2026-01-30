// store/modules/auth.js
import axios from 'axios';
import router from '../../router';
import apiBaseUrl from '../../apiBaseUrl';

const state = {
  user: null,
  roles: [],
  permissions: [],
  isLoaded: false,
};

const getters = {
  hasRole: (state) => (...role) => role.every(r => state.roles.includes(r)),
  hasAnyRole: (state) => (...role) => role.some(r => state.roles.includes(r)),
  hasPermission: (state) => (...permission) => permission.every(p => state.permissions.includes(p)),
  hasAnyPermission: (state) => (...permission) => permission.some(p => state.permissions.includes(p)),
};

const actions = {
  async getUser({ commit }) {
    const token = localStorage.getItem('access_token');
    if (!token) return router.push('/login');

    try {
      const userResp = await axios.get(apiBaseUrl + '/api/auth/init', {
        headers: { Authorization: `Bearer ${token}` }
      });

      commit('setUser', userResp.data.user);

      const rolesPermResp = await axios.get(apiBaseUrl + '/api/user/roles_permissions', {
        headers: { Authorization: `Bearer ${token}` }
      });

      commit('setRoles', rolesPermResp.data.user_roles);
      commit('setPermissions', rolesPermResp.data.user_permissions);
      commit('setLoaded', true);

    } catch (error) {
      localStorage.removeItem('access_token');
      router.push('/login');
    }
  }
};

const mutations = {
  setUser(state, user) { state.user = user; },
  setRoles(state, roles) { state.roles = roles; },
  setPermissions(state, permissions) { state.permissions = permissions; },
  setLoaded(state, val) { state.isLoaded = val; }
};

export default { namespaced: true, state, getters, actions, mutations };
