/**
 * Device Service
 * API functions cho Devices resource
 */

import client from './client';

export const deviceService = {
  /**
   * Lấy tất cả devices
   * @returns {Promise}
   */
  getAll() {
    return client.get('/devices');
  },

  /**
   * Lấy device theo ID
   * @param {number} id
   * @returns {Promise}
   */
  getById(id) {
    return client.get(`/devices/${id}`);
  },

  /**
   * Tạo device mới
   * @param {Object} data - Device data
   * @returns {Promise}
   */
  create(data) {
    return client.post('/devices', data);
  },

  /**
   * Cập nhật device
   * @param {number} id
   * @param {Object} data - Device data
   * @returns {Promise}
   */
  update(id, data) {
    return client.put(`/devices/${id}`, data);
  },

  /**
   * Xóa device
   * @param {number} id
   * @returns {Promise}
   */
  delete(id) {
    return client.delete(`/devices/${id}`);
  },
};

