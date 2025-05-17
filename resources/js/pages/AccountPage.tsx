import { router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useEffect, useState, useRef } from 'react';
import { useAlert } from '@/context/AlertContext';
import { useConfirm } from '@/context/ConfirmationContext';
import { Navbar } from '@/components/Navbar';
import Header from '@/components/Header';
import SearchComponent from '@/components/SearchComponent';
import YearFilterComponent from '@/components/YearFilterComponent';
import ArchivesComponent from '../components/ArchiveComponent';
import { RssSubscribeLink } from '../components/RssSubscribeLink';
import RecentActivityFeed from '../components/RecentActivityFeed';
import Comments from '@/components/Comments';
import '../../css/app.css';

interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  is_subscribed: boolean;
  is_anonymous: boolean;
}

interface AccountPageProps {
  user: User;
}

interface BlogPostType {
  id: number;
  title: string;
  content: string;
  topic: string;
  author: string;
  created_at: string;
  image_url?: string | null;
  updated_at?: string;
  _id?: string;
  slug?: string;
  [key: string]: any;
}

interface PaginatedPosts {
  current_page: number;
  data: BlogPostType[];
}

interface PostPageProps {
  posts: BlogPostType[];
  allPosts: PaginatedPosts;
  topics: string[];
  currentTopic: string | null;
  currentPage: number;
  hasMore: boolean;
  total: number;
  user: { name: string } | null;
  auth?: {
    user: {
      is_admin: boolean;
      name: string;
    } | null;
  };
  [key: string]: any;
}

export default function AccountPage({ user }: AccountPageProps) {
  const { props } = usePage<PostPageProps>();

  useEffect(() => {
    if (!user || user.is_anonymous) {
      router.visit('/'); // Redirect anonymous users to the homepage
    }
  }, [user]);

  if (!user || user.is_anonymous) {
    return null; // Prevent rendering for anonymous users
  }

  const allPostsData = props?.allPosts?.data || [];

const pageUser = usePage().props.user as User;
  const [newEmail, setNewEmail] = useState(pageUser.email);
  const [isSubscribed, setIsSubscribed] = useState(pageUser.is_subscribed);
  const [loading, setLoading] = useState(false);

  const currentPasswordRef = useRef<HTMLInputElement>(null);
  const newPasswordRef = useRef<HTMLInputElement>(null);
  const confirmPasswordRef = useRef<HTMLInputElement>(null);

  const { showAlert } = useAlert();
  const { confirm } = useConfirm();

  const handleUpdateEmail = () => {
    setLoading(true);
    router.post('/update-email', { email: newEmail }, {
      onSuccess: () => {
        showAlert('Email updated successfully!', 'success');
        setLoading(false);
      },
      onError: () => {
        showAlert('Failed to update email', 'error');
        setLoading(false);
      },
    });
  };

  const handleUpdatePassword = () => {
    const currentPassword = currentPasswordRef.current!.value ?? '';
    const newPassword = newPasswordRef.current!.value ?? '';
    const confirmPassword = confirmPasswordRef.current!.value ?? '';

    if (newPassword.length < 8) {
      showAlert('Password must be at least 8 characters long', 'error');
      return;
    }

    if (newPassword !== confirmPassword) {
      showAlert('Passwords do not match', 'error');
      return;
    }

    setLoading(true);
    router.post('/update-password', {
      current_password: currentPassword,
      password: newPassword,
      password_confirmation: confirmPassword,
    }, {
      onSuccess: () => {
        showAlert('Password updated successfully!', 'success');
        setLoading(false);
        if (currentPasswordRef.current) currentPasswordRef.current.value = '';
        if (newPasswordRef.current) newPasswordRef.current.value = '';
        if (confirmPasswordRef.current) confirmPasswordRef.current.value = '';
      },
      onError: () => {
        showAlert('Failed to update password', 'error');
        setLoading(false);
      },
    });
  };

const handleSubscriptionChange = () => {
  setLoading(true);

  const route = isSubscribed
    ? '/account/unsubscribe-newsletter' // Unsubscribe route
    : '/account/subscribe-newsletter'; // Subscribe route

  router.post(route, {}, {
    onSuccess: () => {
      setIsSubscribed((prev) => !prev);
      showAlert(isSubscribed ? 'Unsubscribed successfully!' : 'Subscribed successfully!', 'success');
      setLoading(false);
    },
    onError: () => {
      showAlert('Failed to update subscription', 'error');
      setLoading(false);
    },
  });
};

  // const handleDeleteAccount = async () => {
  //   const confirmed = await confirm({
  //     title: 'Delete Account',
  //     message: 'Are you sure you want to delete your account? This action cannot be undone.',
  //     confirmText: 'Delete',
  //     cancelText: 'Cancel',
  //     type: 'danger',
  //   });

  //   if (!confirmed) return;

  //   setLoading(true);
  //   router.post('/delete-account', { user_id: user.id }, {
  //     onSuccess: () => {
  //       showAlert('Account deleted successfully!', 'success');
  //       setLoading(false);
  //       router.get('/');
  //     },
  //     onError: () => {
  //       showAlert('Failed to delete account', 'error');
  //       setLoading(false);
  //     },
  //   });
  // };

const handleDeleteAccount = async () => {
  console.log('handleDeleteAccount started');

  const confirmed = await confirm({
    title: 'Delete Account',
    message: 'Are you sure you want to delete your account? This action cannot be undone.',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    type: 'danger',
  });

  console.log('Delete account confirmed:', confirmed);

  if (!confirmed) return;

  const removeCommentsConfirmed = await confirm({
    title: 'Remove Comments?',
    message: 'Do you want to remove all your comments?',
    confirmText: 'Yes, remove comments',
    cancelText: 'No, keep comments',
    type: 'warning',
  });

  console.log('Remove comments confirmed:', removeCommentsConfirmed);

  setLoading(true);
  console.log('Sending request to delete account with remove_comments:', removeCommentsConfirmed ? 'yes' : 'no');
  router.post('/account/delete', {
    user_id: user.id,
    remove_comments: removeCommentsConfirmed ? 'yes' : 'no',
  }, {
    onSuccess: () => {
      console.log('Account deleted successfully!');
      showAlert('Account deleted successfully!', 'success');
      setLoading(false);
      router.get('/');
    },
    onError: () => {
      console.log('Failed to delete account');
      showAlert('Failed to delete account', 'error');
      setLoading(false);
    },
  });
};

  return (
    <>
      <Navbar />
      <Header />
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
  <main className="!p-4 md:!p-8">
    <div className="w-full flex flex-col items-center justify-center lg:flex-row lg:items-start md:!gap-12">
      <aside className="w-full lg:w-120 lg:!mb-0 xl:!ml-30 2xl:!ml-40 overflow-y-auto xl:!mt-0">
        <div className="lg:sticky lg:top-24 !space-y-4 md:!space-y-6 w-full lg:!w-80 xl:!w-100">
          <div className="rounded-lg !p-4">
            <h3 className="font-semibold !mb-2">Account</h3>
            <p className="opacity-80">
              Here you can manage your account settings. You can update your email, password, and subscription status. If you don't need your account anymore, you can delete it.
            </p>
          </div>

          <div className="rounded-lg bg-[#5800FF]/10 !p-4">
            <SearchComponent posts={allPostsData} />
            <YearFilterComponent posts={allPostsData} />
            <ArchivesComponent />
            <RssSubscribeLink />
            <RecentActivityFeed />
          </div>
        </div>
      </aside>

      {/* Main content container */}
      <div className="flex flex-col items-center justify-center lg:flex-row lg:gap-0 w-full lg:w-2/3">
        {/* My Account Section */}
        <div className="flex flex-col items-center w-4/5 lg:!w-1/2 bg-[var(--bg-primary)] text-[var(--text-primary)]">
          <h2 className="text-3xl font-bold !mb-4 md:!mb-10 !mt-10 text-center">My Account</h2>

          <div className="bg-white rounded-lg border-2 border-[#5800FF] shadow-md shadow-[#5800FF] !p-6 !space-y-4 w-full max-w-md mx-auto">
            <div>
              <p className="text-gray-600 !pb-2">Name:</p>
              <p className="text-lg font-semibold !pb-2 text-black">{user.name}</p>
            </div>

            <div>
              <p className="text-gray-600">Email:</p>
              <input
                type="email"
                value={newEmail}
                onChange={(e) => setNewEmail(e.target.value)}
                className="!mt-1 !p-4 border rounded w-full text-black"
              />
              <button
                onClick={handleUpdateEmail}
                disabled={loading}
                className="!mt-2 bg-[#5800FF] hover:bg-[#E900FF] text-white font-bold !py-2 !px-4 rounded w-full"
              >
                {loading ? 'Updating...' : 'Update Email'}
              </button>
            </div>

            <div>
              <p className="text-gray-600">Current Password:</p>
              <input
                type="password"
                ref={currentPasswordRef}
                className="!mt-1 !p-2 border rounded border-gray-900 w-full text-black"
              />

              <p className="text-gray-600 mt-4">New Password:</p>
              <input
                type="password"
                ref={newPasswordRef}
                className="!mt-1 !p-2 border rounded border-gray-900 w-full text-black"
              />

              <p className="text-gray-600 mt-4">Confirm New Password:</p>
              <input
                type="password"
                ref={confirmPasswordRef}
                className="!mt-1 !p-2 border rounded border-gray-900 w-full text-black"
              />

              <button
                onClick={handleUpdatePassword}
                disabled={loading}
                className="!mt-2 bg-[#5800FF] hover:bg-[#E900FF] text-white font-bold !py-2 !px-4 rounded w-full"
              >
                {loading ? 'Updating...' : 'Update Password'}
              </button>
            </div>

            <div>
              <p className="text-gray-600">Newsletter Subscription:</p>
              <button
                onClick={handleSubscriptionChange}
                disabled={loading}
                className={`!mt-2 ${isSubscribed ? 'bg-red-500' : 'bg-green-500'} hover:opacity-80 text-white font-bold !py-2 !px-4 rounded w-full`}
              >
                {isSubscribed ? 'Unsubscribe' : 'Subscribe to Newsletters'}
              </button>
            </div>

            <div>
              <button
                onClick={handleDeleteAccount}
                disabled={loading}
                className="!mt-6 bg-red-500 hover:bg-red-600 text-white font-bold !py-2 !px-4 rounded w-full"
              >
                {loading ? 'Deleting...' : 'Delete Account'}
              </button>
            </div>
          </div>
        </div>

        {/* Comments Section */}
        <div className="w-full lg:w-1/2 flex flex-col items-center justify-start">
          <Comments userId={user.id} />
        </div>
      </div>
    </div>
  </main>
</div>
    </>
  );
}
