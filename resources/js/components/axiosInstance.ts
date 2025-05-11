// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: process.env.NODE_ENV === 'production' ? 'https://your-production-api-url' : 'http://127.0.0.1:8000', // Dynamically set the base URL depending on environment
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
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
