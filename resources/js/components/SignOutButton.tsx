import React from 'react';
import { usePage, router } from '@inertiajs/react';
import { getCsrfToken } from '../components/auth'; // Adjust path if needed
import { useAlert } from '../context/AlertContext';

const SignOutButton = () => {
  const { showAlert } = useAlert();

  const handleLogout = async () => {
    await getCsrfToken();
    router.post('/logout', {}, {
      onFinish: () => console.log('Logged out'),
      onSuccess: () => {
        showAlert('You have signed out!', 'success');
      },
    });
  };

  return (
    <button onClick={handleLogout}>
      Sign Out
    </button>
  );
};

export default SignOutButton;