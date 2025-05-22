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
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    fetch('https://blog-app-production-16c2.up.railway.app/api/recent-activity')
      .then(res => res.json())
      .then((data: Activity[]) => {
        setActivities(data.slice(0, 9)); // Limit to latest 10
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  const SkeletonItem = () => (
    <div className="animate-pulse !px-2 !py-1 rounded bg-gray-800">
      <div className="h-4 bg-gray-700 rounded w-3/4 mb-1"></div>
      <div className="h-3 bg-gray-700 rounded w-1/3"></div>
    </div>
  );

  return (
    <div className="rounded-lg !pb-4 !mt-6">
      <h3 className="font-semibold !mb-2">Recent Activity</h3>

      {loading ? (
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
        <ul className="!space-y-1 max-h-100 overflow-y-auto">
          {activities.map((activity, index) => (
            <li key={index}>
              {activity.type === 'post' ? (
                <a
                  href={activity.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="block w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                >
                  <span className="font-medium">📝 New post: {activity.title}</span>
                  <span className="text-xs opacity-60 block">{activity.createdAt}</span>
                </a>
              ) : (
                <a
                  href={activity.postUrl || '#'}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="block w-full text-left !px-2 !py-1 rounded hover:bg-[#5800FF]/20"
                >
                  <span className="font-medium">
                    💬 New comment to: {activity.postTitle && `${activity.postTitle}`}
                  </span>
                  <span className="text-xs opacity-60 block">{activity.createdAt}</span>
                </a>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default RecentActivityFeed;
