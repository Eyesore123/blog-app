# Blog App

![Capture2](https://github.com/user-attachments/assets/d7b27022-45c0-47f7-b1b8-70adfe710f4f)

# Made with: Laravel + React + Vite + Tailwind CSS + Inertia

## Introduction

- This is a blog app that I'm going to hook up with my portfolio site. Work in progress!

So currently app works like a basic blog app: it shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu, showing the posts for each year. There are archive pages which show all the posts for each year (paginated). Posts use tags (optional) and posts can be filtered by tags just by clicking a tag on the post.

RSS Feed component is also included. I added custom API endpoint for recent activity, which is used in recent activity component in landing page. I also created "the latest post" endpoint which can be used to fetch the latest post. It's an alternative to rss feed (I plan to use it for my portfolio site).

Admin can add new posts, edit posts and delete comments. Admin can also deactivate and - as an ultimate solution - delete accounts. Logged in users can add comments, edit their comments and delete comments when there are no replies. Comments are rate limited by IP address (10 comments per day), and there are no Captchas because IP address guarantees that the limiter applies to many users from the same IP address. Rate limiter is done using custom RateLimitService class. Likewise SEO is done using a custom SEO class and then provided for the app using react-helmet-async package (it was the easiest solution considering I'm not using blade views).

Logged in users have a My Account page where they can change their password, delete their account and subscribe to a newsletter. Subscribed users get the latest blogpost sent to them automatically. Login view has a "forgot password" section so user can reset their password using email. When a user deletes account, user can decide to keep the comments or delete them from blog posts. If user chooses to keep comments, comments are kept but name is changed to "anonymous", because deleted users can't be identified and is no longer attached to any comment. I think it's nice to offer the option to either keep or delete comments.

Providers are used for themes, alerts and confirmations. I added a markdown editor for posts. Perhaps I could add one for comments, but it was quite bothersome to get it work without errors for posts so I'm not sure if I'll add new editors.

Loading spinners are used for images and log-in. Custom alerts pop up to notify user of successful logout. Login doesn't include popup, because it would feel a bit intrusive towards regular users. Admin gets a pop up notification when a new post is added. Custom dialogue window in used for verifying important actions (like deleting a post or a user account). Admin can use Google Cloud Tranlation API to translate posts to other languages.

Errors are mostly handled with custom error pages. Images use a fallback image in case the image is not found.

Automated backups - Php scripts are used for controlling backups. Cron jobs are set up with GitHub Actions to back up the db in regular intervals.

Postgres admin panel is added to make the backend adjustments easier. Backend admin can run scripts, make changes to users and tables, create new admins etc. Only admin has access to the admin panel and scripts. Symlink is recreated via script after each deployment. Currently Vite assets need also rebuilding after a new deployment.

Blog still needs some work, though, including:

1. Post translation save to database and fetch for translated posts
2. Language toggle to navbar (global translations and post translations)
3. Advanced features for admin (image size adjustments? etc.)
4. Email subscription options in admin panel + an improved template for blog post email
5. Profile images? - would be a nice addition
6. Bookmark icon from Laravel icon to my own
7. Suggestions for posts based on tags (two max, only on post page)
8. New comment notification to admin (timed, once per day). Notification to registered users when someone replies to their comment.
9. Scheduled uploads feature would be nice
10. Save sketches feature + fetch sketches from database in admin panel

I deployed this app on Railway with two services, one for app (both frontend and backend use the same url) and one for database (PostgreSQL). Railway uses a volume to store the images so they are not lost after a new deployment.

Comment section looks like this when user is not signed in. Signed in users can reply to comments and edit/delete comments.

![blog3](https://github.com/user-attachments/assets/9b47ad5c-13f9-4858-9291-1eb1d2397d96)

Portgres admin panel looks nice:

![admindashboard](https://github.com/user-attachments/assets/0530e3cf-617f-4e1a-974d-68bf35c829f2)


## What I've learned during this project

- How to use Laravel with Inertia. It's a great way to use React with Laravel. Blade views are not used (app.blade.php is the only blade file), but react components are used and coupled with Laravel classes, models, controllers and routing. This is great for performance and SEO. Laravel backend works normally and React frontend works normally. Inertia is used to render the React components on the client side with backend data, with the classic server-side routing that still has the SPA feel and features (React).
- How to add custom API endpoints to Laravel.
- How to use models, controllers, routes and views in Laravel (views that are rendered with Inertia).
- How to add and use a markdown editor.
- How to use Mailtrap for email testing in a sandbox, and also use it for testing backend routes. Previously I've used SendGrid for sending emails (in my Next.js web shop), but Mailtrap seems to be very easy to use for testing purposes.
- How to set up rate limiters in Laravel.
- How to add admin privileges securely and how to force https in Laravel. Previously I've added admin privileges by changing user data in db, but now it was created with a secure create-admin route (with middleware and a random token). Forcing https was done using middleware and rules, and it was a bit more tricky than the usual approach, like setting the rules in .htaccess file or forcing it on the server.
- How to add a queue for sending emails and set up the database queue table and a worker + how to use the database driver in Laravel.
- How to use sqlite db in test environment and then switch to Postgres in production.
- How to use Railway services, Railway CLI and how connect app to db in Railway.
- I was thinking of sending my blog posts automatically to LinkedIn, but because LinkedIn has become so heavy-handed and frustrating with its unnecessary security measures, I will not support it. Blog users can still share posts on LinkedIn but I won't be adding mine there.
- It is quite painful to get all the routes and controllers working without hiccups. One subtle change anywhere can break the whole thing. For example, I decided to make changes to account removal logic and suddenly I had to make changes not only to frontend but also to user model, comment model, Accountcontroller, Commentcontroller and to user table with additional migrations. 
- Deploying can also be a pain if you don't know all the ins and outs of the deployment process. I've never deployed a Laravel app before so I had to learn a lot about it. How Laravel caching works, how images should be loaded (and stored), how to set cors policies properly etc.
- Vite was causing more issues than usual in my Laravel setup. I had to add bash scripts and other scripts, a Vite helper, and an htaccess file, and then I had to make some extra changes to providers and vite config file just to get the vite build to work.

## Issues

- Markdown editor needs some work.
- Flash messages are not working properly so I made a workaround for pop-ups. Pop-ups work fine but they are not really flash messages.