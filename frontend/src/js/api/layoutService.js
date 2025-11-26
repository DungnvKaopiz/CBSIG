/**
 * Layout Service
 * API functions cho Layouts resource
 */

import client from './client';

export const layoutService = {
  /**
   * Lấy tất cả layouts
   * @returns {Promise}
   */
  getAll() {
    return client.get('/layouts');
  },

  /**
   * Lấy layout theo ID
   * @param {number} id
   * @returns {Promise}
   */
  getById(id) {
    return client.get(`/layouts/${id}`);
  },

  /**
   * Tạo layout mới
   * @param {Object} data - Layout data
   * @returns {Promise}
   */
  create(data) {
    return client.post('/layouts', data);
  },

  /**
   * Cập nhật layout
   * @param {number} id
   * @param {Object} data - Layout data
   * @returns {Promise}
   */
  update(id, data) {
    return client.put(`/layouts/${id}`, data);
  },

  /**
   * Xóa layout
   * @param {number} id
   * @returns {Promise}
   */
  delete(id) {
    return client.delete(`/layouts/${id}`);
  },
};

