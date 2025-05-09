// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: 'http://127.0.0.1:8000', // fine
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

axiosInstance.interceptors.request.use(function (config) {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }
  return config;
}, function (error) {
  return Promise.reject(error);
});

export default axiosInstance;
