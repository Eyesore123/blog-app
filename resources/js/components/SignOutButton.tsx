import { router } from "@inertiajs/react";
import '../../css/app.css';

export default function SignOutButton() {
  function handleSignOut() {
    router.post("/logout", {}, {
      onSuccess: () => {
        // Optionally show snackbar or UI feedback
        console.log("Signed out");
      },
    });
  }

  return (
    <button onClick={handleSignOut}>
      Sign out
    </button>
  );
}
