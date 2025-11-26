/**
 * Layout Item Service
 * API functions cho Layout Items (frames) resource
 */

import client from './client';

export const layoutItemService = {
  /**
   * Lấy tất cả layout items
   * @returns {Promise}
   */
  getAll() {
    return client.get('/layout-items');
  },

  /**
   * Lấy layout item theo ID
   * @param {number} id
   * @returns {Promise}
   */
  getById(id) {
    return client.get(`/layout-items/${id}`);
  },

  /**
   * Lấy layout items theo layout ID
   * @param {number} layoutId
   * @returns {Promise}
   */
  getByLayout(layoutId) {
    return client.get(`/layout-items/layout/${layoutId}`);
  },

  /**
   * Tạo layout item mới
   * @param {Object} data - Layout item data
   * @returns {Promise}
   */
  create(data) {
    return client.post('/layout-items', data);
  },

  /**
   * Cập nhật layout item
   * @param {number} id
   * @param {Object} data - Layout item data
   * @returns {Promise}
   */
  update(id, data) {
    return client.put(`/layout-items/${id}`, data);
  },

  /**
   * Xóa layout item
   * @param {number} id
   * @returns {Promise}
   */
  delete(id) {
    return client.delete(`/layout-items/${id}`);
  },
};

