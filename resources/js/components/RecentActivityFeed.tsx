import React, { useEffect, useState } from 'react';

type PostActivity = {
  type: 'post';
  title: string;
  url: string;
  createdAt: string;
};

type CommentActivity = {
  type: 'comment';
  excerpt: string;
  postTitle?: string | null;
  postUrl?: string | null;
  createdAt: string;
};

type Activity = PostActivity | CommentActivity;

const RecentActivityFeed: React.FC = () => {
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(true);
  const [firstLoad, setFirstLoad] = useState(true);
  const [showSkeleton, setShowSkeleton] = useState(false);

  useEffect(() => {
    const abortController = new AbortController();

    const fetchActivities = async () => {
      try {
        const res = await fetch('https://blog-app-production-16c2.up.railway.app/api/recent-activity', {
          signal: abortController.signal,
        });
        const data: Activity[] = await res.json();
        setActivities(data.slice(0, 9)); // limit to 9
      } catch (err: any) {
        if (err.name !== 'AbortError') {
          console.error(err);
        }
      } finally {
        setLoading(false);
      }
    };

    fetchActivities();

    return () => {
      abortController.abort();
    };
  }, []);

  useEffect(() => {
    if (!loading) setFirstLoad(false);
  }, [loading]);

  useEffect(() => {
    if (firstLoad && loading) {
      const timeoutId = setTimeout(() => setShowSkeleton(true), 500);
      return () => clearTimeout(timeoutId);
    }
  }, [firstLoad, loading]);

  const SkeletonItem = () => (
    <div className="animate-pulse !px-2 !py-1 rounded bg-gray-800">
      <div className="h-4 bg-gray-700 rounded w-3/4 mb-1"></div>
      <div className="h-3 bg-gray-700 rounded w-1/3"></div>
    </div>
  );

  return (
    <div className="rounded-lg !pb-4 !mt-6">
      <h3 className="font-semibold !mb-2">Recent Activity</h3>

      {firstLoad && loading && showSkeleton ? (
        <ul className="!space-y-1">
          {[...Array(3)].map((_, idx) => (
            <li key={idx}>
              <SkeletonItem />
            </li>
          ))}
        </ul>
      ) : activities.length === 0 ? (
        <p className="text-sm opacity-60 italic">No recent activity.</p>
      ) : (
        <ul className="!space-y-1 max-h-96 overflow-y-auto pr-1">
          {activities.map((activity, index) => (
            <li key={index}>
              <a
                href={activity.type === 'post' ? activity.url : activity.postUrl || '#'}
                target="_blank"
                rel="noopener noreferrer"
                className="block w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
              >
                <span className="font-medium">
                  {activity.type === 'post'
                    ? `📝 New post: ${activity.title}`
                    : `💬 New comment to: ${activity.postTitle || 'unknown post'}`}
                </span>
                <span className="text-xs opacity-60 block">{activity.createdAt}</span>
              </a>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default React.memo(RecentActivityFeed);
