# Blog App

![Capture2](https://github.com/user-attachments/assets/d7b27022-45c0-47f7-b1b8-70adfe710f4f)

# Made with: Laravel + React + Tailwind CSS + Inertia

## Introduction

- This is a blog app that I'm going to hook up with my portfolio site. Work in progress!

So currently app shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu, showing the posts for each year. There are archive pages which show all the posts for each year (paginated). Posts use tags (optional) and posts can be filtered by tags just by clicking a tag on the post.

RSS Feed component is also included. I added custom API endpoint for recent activity, which is used in recent activity component in landing page. I also created "the latest post" endpoint which can be used to fetch the latest post. It's an alternative to rss feed (I plan to use it for my portfolio site).

Admin can add new posts, edit posts and delete comments. Admin can also deactivate and - as an ultimate solution - delete accounts. Logged in users can add comments, edit their comments and delete comments when there are no replies. Comments are rate limited by IP address (10 comments per day), and there are no Captchas because IP address guarantees that the limiter applies to many users from the same IP address. Rate limiter is done using custom RateLimitService class. Likewise SEO is done using a custom SEO class and then provided for the app using react-helmet-async package (it was the easiest solution considering I'm not using blade views).

Logged in users have a My Account page where they can change their password, delete their account and subscribe to a newsletter. Login view has a "forgot password" section so user can reset their password using email.

Providers are used for themes, alerts and confirmations. I added a markdown editor for posts. Perhaps I could add one for comments, but it was quite bothersome to get it work without errors for posts so I'm not sure if I'll add new editors.

Blog still needs some work, though, including:

1. Installation of AI translation package (Google/DeepL)?
2. Auto-translate on post save + store in DB
3. Language toggle to navbar
4. Toggle button in navbar loads correct language
5. Advanced features for admin (image size adjustments? etc.)
6. Email subscription (latest blog post in newsletter) backend and admin panel
7. Blog posts get automatically uploaded to LinkedIn
8. Loading spinners for images with uniform style (also for login?)

![blog3](https://github.com/user-attachments/assets/9b47ad5c-13f9-4858-9291-1eb1d2397d96)

## What I've learned during this project

- How to use Laravel Inertia. It's a great way to use React with Laravel. Blade views are not used but react components coupled with Laravel classes, models, controllers and routing. This is great for performance and SEO. Laravel backend works normally and React frontend works normally, but inertia is used to render the React components on the client side with backend data, with the classic server-side routing that still has the SPA feel and features (React).
- How to add custom API endpoints to Laravel.
- How to use models, controllers, routes and views in Laravel (views that are rendered with Inertia).
- How to add and use a markdown editor.
- How to use Mailtrap for testing emails in a sandbox, and also use it for testing backend routes. Previously I've used SendGrid for sending emails (in my Next.js web shop), but Mailtrap seems to be very easy to use for testing purposes.