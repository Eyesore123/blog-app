// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: process.env.NODE_ENV === 'production'
    ? 'https://blog-app-production-16c2.up.railway.app'
    : 'http://127.0.0.1:9000',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'   // Keep Accept, but REMOVE Content-Type
  },
  withCredentials: true
});

// Request Interceptor to add CSRF token to headers
axiosInstance.interceptors.request.use(function (config) {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }
  return config;
}, function (error) {
  return Promise.reject(error);
});

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      console.error('Unauthorized - Redirecting to login...');
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
