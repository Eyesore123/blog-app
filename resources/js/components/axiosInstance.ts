// axiosInstance.ts
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: 'http://127.0.0.1:8000', // fine
  withCredentials: true,            // sends cookies
  timeout: 10000,
});

export default axiosInstance;
