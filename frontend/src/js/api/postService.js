/**
 * Post Service
 * API functions cho Posts resource
 */

import client from './client';

export const postService = {
  /**
   * Lấy tất cả posts
   * @returns {Promise}
   */
  getAll() {
    return client.get('/posts');
  },

  /**
   * Lấy post theo ID
   * @param {number} id
   * @returns {Promise}
   */
  getById(id) {
    return client.get(`/posts/${id}`);
  },

  /**
   * Tạo post mới
   * @param {Object} data - { title, content }
   * @returns {Promise}
   */
  create(data) {
    return client.post('/posts', data);
  },

  /**
   * Cập nhật post
   * @param {number} id
   * @param {Object} data - { title, content }
   * @returns {Promise}
   */
  update(id, data) {
    return client.put(`/posts/${id}`, data);
  },

  /**
   * Xóa post
   * @param {number} id
   * @returns {Promise}
   */
  delete(id) {
    return client.delete(`/posts/${id}`);
  },
};

