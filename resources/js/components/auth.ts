import axiosInstance from './axiosInstance';

export async function getCsrfToken() {
  try {
    // Make a GET request to get the CSRF token
    await axiosInstance.get('/sanctum/csrf-cookie');
  } catch (error) {
    console.error("Error while getting CSRF token:", error);
  }
}
