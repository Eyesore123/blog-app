// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL:
    process.env.NODE_ENV === 'production'
      ? 'https://blog.joniputkinen.com' // Laravel backend in production
      : 'http://127.0.0.1:8000',        // Laravel backend in dev
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json', // Laravel expects JSON
    // ❌ Do NOT manually set 'Content-Type'; Axios will handle it
  },
  withCredentials: true, // ✅ Important for Laravel Sanctum / cookies
});

export default axiosInstance;
