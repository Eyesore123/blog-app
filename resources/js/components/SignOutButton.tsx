import { Inertia } from '@inertiajs/inertia';
import '../../css/app.css';

export default function SignOutButton() {
  const handleSignOut = () => {
    // Inertia.js makes an API call to the Laravel route to log out
    Inertia.post(route('logout'));
  };

  return (
    <button onClick={handleSignOut}>
      Sign Out
    </button>
  );
}
