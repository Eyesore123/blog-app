# Blog App

<img width="2542" height="1309" alt="blogimage2" src="https://github.com/user-attachments/assets/51e2f9db-f480-43a2-8b49-b0e1772c4501" />

# Made with: Laravel + React + Vite + Tailwind CSS + Inertia

## Introduction

- This is a blog app that is used in conjunction with my portfolio site. Work in progress!

- Now I'm working on a news controller which lets me write news to users dynamically. 

This app works like a basic blog app: it shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu. There are archive pages which show all the posts for each year (paginated). Posts use tags (optional) and posts can be filtered by tags just by clicking a tag on the post.

Admin can add new posts, edit posts and delete comments. Admin can add new images to database from a link or from local storage and change post images. Admin can also deactivate and - as an ultimate solution - delete accounts. Logged in users can add comments, edit their comments and delete comments when there are no replies.

All registered users need to very email address via a link sent to their email address. This is done using Laravel's built-in email verification feature.

Registered users (not anonymous) have a My Account page where they can change their password and delete their account. When an account is deleted, user can decide to keep the comments or delete them from blog posts. If user chooses to keep comments, comments are kept but name is changed to "anonymous", because deleted users can't be identified and is no longer attached to any comment. I think it's nice to offer the option to either keep or delete comments.

Registered users have a toggle option in My Account page: they can choose to receive email notifications when a new post is added (showing the post content). There's also a separate option to get a notification when someone has replied to their comment.

Login page view has a "forgot password" section so user can reset their password using email.

Registered users can add or change their profile image that's shown in the navbar. Anon users have a Guy Fawkes mask as their profile picture, and registered users who don't have a profile image have a default user icon image.

Comments are hidden on the landing page but revealed by default on the post page. Users see two suggested posts based on the tags of the current post (post page only).

RSS Feed component is included in the app. Users see the recent activity on the main page (posts and comments).

Admin gets notifications of all new comments and a post notification to email when a new post is added.

Admin can use Google Cloud Translation API to translate posts to other languages.

Admin has an additional panel (front-end) in admin dashboard that lets admin send emails to users. Options: to everyone / admins / subbed users / users. Admin can also send a test post send to selected email.

Admin can store images and videos from admin dashboard to a database. Admin can also fetch images and videos from db and show them as a list and delete items.


## Styles

App uses only a few colors that I've personally picked to match the colors of my portfolio site, including: #000000; #ffffff; #ffc600; #e900ff; transparent: rgba(255, 255, 255, 0.05); linear-gradient(to right, #e900ff, #ffc600); linear-gradient(to right, #5800ff, #e900ff);

UseTheme hook is used to change the color consistently across the app. User clicks on the button and it changes the colors of texts and backgrounds.

Loading spinners are used for images and log-in. Custom alerts pop up to notify user of successful logout. Login doesn't include popup, because it would feel a bit intrusive towards regular users. Admin gets a pop up notification when a new post is added. Custom dialogue window in used for verifying important actions (like deleting a post or a user account).

Lucide-react package is used for some of the icons.

The fanciest stylistic decision is the use of framer-motion in the unemployment counter component. When a user gives a virtual hug, it sends hearts flying towards the top of the screen.

## Error handling + backend

Images use a fallback image in case the image is not found so there should be an image shown even when the requested resource is not available.

Error handling and what is shown to user depends on what causes the error. If the user is 'lost' and the requested page doesn't exist, the custom default 404 error page is shown. Errorboundary is used for error handling, but it's mostly decorative and shouldn't be triggered too often, and the same is true for exception handler. In all typical error cases user gets an error page, either served by Laravel or by custom routes and pages. 

---------------------------------------------

Postgres admin panel is added to make the backend adjustments easier. There are two reasons for this. First, admin can make changes to values in postgres tables when production database is "locked down". No need to go change values in the database itself. Second, scripts add more robustness to the blog making it more environment-dependent. If the user decides to change host, it is easier to do that.

Backend admin can run scripts, make changes to users and tables, create new admins etc. Only admins have access to admin panel and scripts. Scripts are categorized to different tasks.

I added a custom API endpoint for recent activity, which is used in recent activity component in landing page. It fetches the latest data from the backend. I also created "the latest post" endpoint which can be used to fetch the latest post. It's an alternative to rss feed.

Comments are rate limited by IP address (10 comments per day), and there are no Captchas because IP address guarantees that the limiter applies to many users from the same IP address. Rate limiter is done using custom RateLimitService class. Likewise SEO is done using a custom SEO class and then provided for the app using react-helmet-async package (it was the easiest solution considering I'm not using blade views).

Automated backups - Php scripts are used for controlling backups. Cron jobs are set up with GitHub Actions to back up the db in regular intervals.

Providers are used for alerts and confirmations. I added a markdown editor for posts to improve the styling of the posts. Perhaps I could add another editor for comments, but it was quite bothersome to get markdown to work properly for posts alone so I probably won't be adding any new editors.

Infobanner component: instead of typing text manually to code and updating text with each deployment, admins can use backend route
and backend script to toggle infotext component and change text dynamically.

Sitemap generator: sitemap is being generated with each new deployment and put into public folder.

# Emails & workers

- Previously I used Gmail STMP for email notifications, but that stopped working for some reason and my mails were blocked. Emails work in this app, but mailer needs to be defined in mail.php and mailer keys in .env.

In my production app I'm moving to use some other mailer, but I need my own domain first. Email notifications are not in use at the moment. 

- Backend scripts include scripts for checking worker jobs and email sending.

## SEO

Currently the most effective SEO method is the JSON-LD script that's placed inside the header:

{
  "@context": "https://schema.org",
  "@type": "Blog",
  "url": "https://blog.joniputkinen.com/",
  "name": "Joni's Blog",
  "description": "A blog about web development, coding, personal projects and life in general.",
  "publisher": {
    "@type": "Person",
    "name": "Joni Putkinen"
  }
}

I also have a Helmet section in each post (BlogPost.tsx) which 'sends' the JSON-LD script as schema data - this helps search engines understand the content and the individual posts better. For example, it might look something like this:

{
  "@context":"https://schema.org",
  "@type":"BlogPosting",
  "mainEntityOfPage":{"@type":"WebPage","@id":"..."},
  "headline":"How to do SEO as a freelancer",
  "description":"When you’re a freelancer, visibility is everything...",
  "image":"/storage/uploads/...",
  "author":{"@type":"Person","name":"Joni Putkinen"},
  "publisher":{"@type":"Person","name":"Joni Putkinen"},
  "datePublished":"2025-09-28T12:12:41.000000Z",
  "dateModified":"2025-09-28T12:37:08.000000Z"
}

Built-in sitemap generator creates a sitemap with each new deployment and places it inside public folder. Sitemap goes to Google immediately either by sending a ping from script or replacing sitemap manually on Google console. Google gets the sitemap eventually even without sending it.

I'll write about SEO once I get it to work the way I want it.

## Planned improvements

Blog still needs some work, though, including:

1. Post translation save to database and fetch for translated posts (partially done)
2. Advanced features for admin (image size adjustments? etc.)
3. Scheduled uploads feature would be nice
4. Customized emails that look better than the default Laravel emails in comment notiifications and email verifications. Default emails are not bad but could be better.
5. Profile image features. I added profile image upload as an extra feature, but noticed that image compression would speed up the site a lot, and so it would be nice to add a feature to compress images. It would also be nice to have a feature to crop images, and use images in the comment section. Without compression I might have to offer images from a selection of smaller size icons.
6. Administrator tags for admins in comment section

## Deployment

I deployed this app on Railway using three separate services/containers:

1. App container – serves both frontend and backend at the same URL.

2. Database container – PostgreSQL.

3. Queue worker container – handles notifications for comments and blog posts.

Railway uses a volume to store images, so they persist across deployments.

Deployment details:

Originally start.sh was the main entrypoint for Railway. Now I split the deployment into two phases: build phase and run phase, each with its own script.

Build phase: bash build.sh (set as the build command in Railway).

Run phase: bash run.sh (set as the start command in Railway).

These commands are executed separately in the Railway dashboard. The old build command from railway.toml is no longer used; it previously called the railway-build script from package.json, which in turn ran start.sh. I keep start.sh in the repo as a fallback in case I want to revert to a single-script deployment.

When adding the sitemap generator, I realized it’s important to keep build and run phases separate because the sitemap generator needs runtime access to the database. Most other tasks would work in a single script, but separating build and runtime tasks makes the deployment cleaner and more reliable.

Some scripts in the repo are only used for local development and testing. start.sh simplifies deployment by reducing manual steps, like fixing symlinks and rebuilding Vite assets. The postbuild script is no longer used in Railway but remains in the repo for potential future use.

## Screenshots

Comment section looks like this when user is not signed in. Signed in users can reply to comments and edit/delete comments.

![blog3](https://github.com/user-attachments/assets/9b47ad5c-13f9-4858-9291-1eb1d2397d96)
<img width="1898" height="949" alt="blogimage" src="https://github.com/user-attachments/assets/c65fca2e-b3de-4d7c-901d-c4c18be86df0" />

Portgres admin panel looks nice:

![admindashboard](https://github.com/user-attachments/assets/0530e3cf-617f-4e1a-974d-68bf35c829f2)

## Issues

- Markdown editor needs some work.
- When a user logs in to add a comment, it takes user back to main page. I was wondering if that could be improved,
similar to how History.back() sends to the exact same page location.
- Flash messages are not working properly so I made a workaround for pop-ups.
- Admin can create and fetch sketches of posts, but sketches are separate from upload panel so the UI is not the most intuitive, and currently it's not filling all the fields
- Aside section is a bit heavy and can disturb the function called page scroll: when user clicks on a link, the page might stop going all the way up when the aside part is still loading. So instead of page scrolling all the way up, it might stop somewhere in between in some instances.

## Testing

- This repo doesn't include a test suite.

Preparations:

Make sure that you have
- Node.js and node package manager installed
- Git bash installed, preferably
- Php and Laravel Herd installed

Then:

- Set suitable cors policy before testing to avoid errors.
- Make sure you're using sqlite database for testing and you've set the right database connection and set it up correctly.
- "npm install" - installs all npm dependencies.
- "composer install" - installs composer dependencies.
- "php artisan migrate" - runs migrations.

Additional:

- Copy .env.example and paste values into a fresh .env. Use command "php artisan key:generate to" create a new encryption key, then paste that key to your .env-file. Also set the port number to the one you use. The other values can stay the same.
- If migration causes errors, check the commands in start.sh and use them after adjustments on a terminal to run migrations one by one on your local dev server.

Running a server (use two different terminals):

- "npm run dev" - runs vite dev server.
- "php artisan serve" - runs laravel server.

-->

After that server runs on port 8000 by default:
http://127.0.0.1:8000

Notice also that this repo is still using a lot of absolute paths. Yes, that was a stupid decision, but I was too focused on getting this blog to work so I let that happen mistakenly. If I later decide to use a custom domain, I might replace absolute paths to relative and test changes in a new branch, but hey, at least it works.

## What I've learned during this project

- How to use Laravel with Inertia. It's a great way to use React with Laravel. Blade views are not used (app.blade.php is the only blade file), but react components are used and coupled with Laravel classes, models, controllers and routing. This is great for performance and SEO. Laravel backend works normally and React frontend works normally. Inertia is used to render the React components on the client side with backend data, with the classic server-side routing that still has the SPA feel and features (React).
- How to pass data from backend to frontend via Inertia
- How to add custom API endpoints to Laravel.
- How to use models, controllers, routes and views in Laravel (views that are rendered with Inertia).
- How to use commands to create my own functionalities and pipelines in Laravel environment in both testing and production
- How to add and use a markdown editor.
- How to use Mailtrap for email testing in a sandbox, and also use it for testing backend routes. Previously I've used SendGrid for sending emails (in my Next.js web shop), but Mailtrap seems to be very easy to use for testing purposes.
- How to set up rate limiters in Laravel.
- How to add admin privileges securely and how to force https in Laravel. Previously I've added admin privileges by changing user data in db, but now it was created with a secure create-admin route (with middleware and a random token) and also by inserting values straight with pgAdmin. Forcing https was done using middleware and rules, and it was a bit more tricky than the usual approach, like setting the rules in .htaccess file or forcing it on the server.
- How to add a queue for sending emails and set up the database queue table and a worker + how to use the database driver in Laravel. Working with emails is very intricate work. Email sending issues can be annoying, because email sends do not always trigger, routes and controllers need to be flawless etc.
- How to use sqlite db in test environment and then switch to Postgres in production.
- How to use Railway services, Railway CLI and how connect app to db in Railway.
- How to use several services or container in tandem in Railway and connect them to each other.
- How to use pgAdmin and connect to db in Railway.
- How to make backups. I had written nearly 20 posts to my blog when I had to restore data from a backup, because I messed up the migrations and had to start migrations from scratch. I had backups set up, but noticed that I had to make some adjustments to sql file before I could use the query tool in pgAdmin to insert the data from the backup. In the future I will make sure to test the backup features properly before I move to production. It's important to ensure that backups can be restored without having to make any manual changes to data and that the backed up data is correct (not truncated or corrupted).
- I was thinking of sending my blog posts automatically to LinkedIn, but because LinkedIn has become so heavy-handed and frustrating with its unnecessary security measures, I will not support it. Blog users can still share posts on LinkedIn but I won't be adding mine there.
- It is quite painful to get all the routes and controllers working without hiccups. One subtle change anywhere can break the whole thing. For example, I decided to make changes to account removal logic and suddenly I had to make changes not only to frontend but also to user model, comment model, Accountcontroller, Commentcontroller and to user table with additional migrations.
- Deployment can also be a pain if you don't know all the ins and outs of the deployment process. I've never deployed a Laravel app before so I had to learn a lot about it. How Laravel caching works, how images should be loaded (and stored), how to set cors policies properly etc.
- Laravel has some default behaviour and structuring that can be extremely hard to override. For example, I spent a lot of time trying to figure out why I can't redirect a user from email link to /login/success route (it went to /login every time). I was trying to keep the user signed out during the email verification process, but it was not working. I tried everything. Eventually I decided to keep the user signed in and redirect to /login/success route after the email verification, and it worked. Lesson learned: it's usually a good idea to follow Laravel's default behaviour. You can't tweak everything. Even if you could, it would require dismantling the Laravel default structure and rebuilding some of the features from scratch.
- Laravel + React + Inertia combo would not be very practical to work with for a large team. That's because making even small changes requires intricate knowledge of the project structure. It's hard to make even slight changes without changing both frontend code and backend code at the same time. Blade views are the default Laravel way of creating templates for the frontend, so using React components instead of blade views can be a bit tricky at times and not for the faint of heart.

- Vite was causing more issues than usual in my Laravel setup. I had to add bash scripts and other scripts, a Vite helper, and an htaccess file, and then I had to make some extra changes to providers and vite config file just to get the vite build to work.

When deploying Laravel + React + Vite on Railway, hashed JS chunks sometimes end up referenced by public/build/manifest.json but only public/build/.vite/manifest.json exists. Laravel’s vite() helper fails silently and components appear missing.

To debug this, I created a small PHP “Vite Debug” page that checks both manifest paths, lists the actual built asset files, and lets me copy the manifest into the expected location or trigger a rebuild. This immediately solved “missing components” issues like my Footer not rendering. I still have to use my Vite Debug page after deployment to
correct the Vite assets