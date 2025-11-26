/**
 * Content Service
 * API functions cho Contents resource
 */

import client from './client';

export const contentService = {
  /**
   * Lấy tất cả contents
   * @returns {Promise}
   */
  getAll() {
    return client.get('/contents');
  },

  /**
   * Lấy content theo ID
   * @param {number} id
   * @returns {Promise}
   */
  getById(id) {
    return client.get(`/contents/${id}`);
  },

  /**
   * Tạo content mới (unified API cho tất cả loại content)
   * 
   * Type mapping (theo migration):
   * - 1 = video (file upload hoặc URL)
   * - 2 = image (file upload hoặc URL)
   * - 3 = playlist
   * - 4 = youtube
   * 
   * @param {Object|FormData} data - Content data
   *   - Nếu là FormData: file upload (type sẽ được tự động detect từ file)
   *   - Nếu là Object: URL hoặc YouTube (type phải được chỉ định)
   * @returns {Promise}
   */
  create(data) {
    // Nếu là FormData (file upload), sử dụng multipart/form-data
    if (data instanceof FormData) {
      return client.post('/contents', data, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    }
    
    // Nếu là Object (URL hoặc YouTube), sử dụng JSON
    return client.post('/contents', data);
  },

  /**
   * Cập nhật content
   * @param {number} id
   * @param {Object} data - Content data
   * @returns {Promise}
   */
  update(id, data) {
    return client.put(`/contents/${id}`, data);
  },

  /**
   * Xóa content
   * @param {number} id
   * @returns {Promise}
   */
  delete(id) {
    return client.delete(`/contents/${id}`);
  },
};

