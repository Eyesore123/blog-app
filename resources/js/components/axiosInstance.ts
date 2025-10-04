// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: process.env.NODE_ENV === 'production'
    ? 'https://blog.joniputkinen.com' // ✅ Laravel backend
    : 'http://127.0.0.1:8000', // ✅ Update this to your Laravel dev port if different
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
    // ❌ Do NOT set 'Content-Type': Laravel/Sanctum sets it automatically
  },
  withCredentials: true // ✅ Needed for Sanctum cookie-based auth
});

// ❗️Optional: You don't need this meta-token for SPAs using Sanctum
// Laravel issues CSRF cookies via /sanctum/csrf-cookie route
// You can remove this whole interceptor unless you're rendering a blade page

// axiosInstance.interceptors.request.use(function (config) {
//   const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
//   if (token) {
//     config.headers['X-CSRF-TOKEN'] = token;
//   }
//   return config;
// }, function (error) {
//   return Promise.reject(error);
// });

// Optional response interceptor

// axiosInstance.interceptors.response.use(
//   (response) => response,
//   (error) => {
//     if (error.response?.status === 401) {
//       console.error('Unauthorized - Redirecting to login...');
//     }
//     return Promise.reject(error);
//   }
// );

export default axiosInstance;
