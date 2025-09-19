import React, { useState, useEffect } from 'react';
import axiosInstance from './axiosInstance';
import { motion, AnimatePresence } from 'framer-motion';
import { useTheme } from '../context/ThemeContext';

interface Heart {
    id: number;
    x: number; // horizontal offset
    duration: number; // animation duration
}

export default function UnemploymentCounter() {
    const { theme } = useTheme();
    const startDate = new Date('2024-06-01T00:00:00');

    const [elapsed, setElapsed] = useState({
        years: 0,
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0
    });

    const [hugs, setHugs] = useState(0);
    const [hearts, setHearts] = useState<Heart[]>([]);
    const [thankYouVisible, setThankYouVisible] = useState(false);
    const [isSending, setIsSending] = useState(false);
    const [hasClicked, setHasClicked] = useState(false);

    // Fetch initial hug count
    useEffect(() => {
        axiosInstance.get('/hugs/count').then(res => setHugs(res.data.count));
    }, []);

    // Timer
    useEffect(() => {
        const interval = setInterval(() => {
            const now = new Date();
            let diff = Math.floor((now.getTime() - startDate.getTime()) / 1000);

            const years = Math.floor(diff / (365 * 24 * 60 * 60));
            diff -= years * 365 * 24 * 60 * 60;

            const days = Math.floor(diff / (24 * 60 * 60));
            diff -= days * 24 * 60 * 60;

            const hours = Math.floor(diff / (60 * 60));
            diff -= hours * 60 * 60;

            const minutes = Math.floor(diff / 60);
            const seconds = diff - minutes * 60;

            setElapsed({ years, days, hours, minutes, seconds });
        }, 1000);

        return () => clearInterval(interval);
    }, []);

    const sendHug = async () => {
        setIsSending(true);
        setHasClicked(true);

        try {
            const response = await axiosInstance.post('/hugs');
            setHugs(response.data.count);

            // Spawn 3–5 hearts with random x offset and duration
            const newHearts: Heart[] = Array.from({ length: Math.floor(Math.random() * 3) + 3 }).map(() => ({
                id: Date.now() + Math.random(),
                x: Math.random() * 120 - 60, // horizontal drift
                duration: Math.random() * 2.5 + 2.5
            }));

            setHearts(prev => [...prev, ...newHearts]);

            // Remove hearts after their animation
            newHearts.forEach(heart => {
                setTimeout(() => {
                    setHearts(prev => prev.filter(h => h.id !== heart.id));
                }, heart.duration * 1000);
            });

            // Show thank you message
            setThankYouVisible(true);
            setTimeout(() => setThankYouVisible(false), 3000);
        } catch (err: any) {
            if (err.response?.status === 429) {
                alert('Too many hugs! Please wait a moment.');
            } else {
                console.error('Error sending hug:', err);
            }
        }

        // Re-enable button after 3 seconds
        setTimeout(() => setIsSending(false), 3000);
    };

    return (
        <div className={`rounded-lg !mt-8 !pb-4 text-left relative overflow-visible ${theme}`}>
            <h3 className='font-semibold !mb-2'>Unemployment Timer</h3>
            <p className='text-sm opacity-90 !mt-1'>Since June 1st, 2024:</p>
            <p className='text-3xl font-bold !mt-4'>
                {elapsed.years}y {elapsed.days}d {elapsed.hours}h {elapsed.minutes}m {elapsed.seconds}s
            </p>

            <div className='!mt-4'>
                <button
                    onClick={sendHug}
                    disabled={isSending}
                    className={`bg-pink-500 hover:bg-pink-600 text-white font-bold !py-2 !px-4 rounded-full !mt-6 !mb-2 relative overflow-visible ${
                        isSending ? 'opacity-60 cursor-not-allowed' : ''
                    }`}
                >
                    {isSending ? 'Receiving...' : 'Send me a hug! 🤗'}
                </button>
                <div className='text-sm opacity-90 !mt-6'>
                    Your virtual hug gives me strength so one day I'll be able to end this streak.
                </div>
            </div>

            <div className='!mt-6 text-sm opacity-90'>Total hugs received: {hasClicked ? hugs : '?'}</div>

            <div className="text-green-500 font-semibold text-sm !mt-2">
                <span className={`${thankYouVisible ? 'opacity-100' : 'opacity-0'} transition-opacity`}>
                    Thank you! 💚
                </span>
            </div>

            {/* Animated hearts */}
            <AnimatePresence>
                {hearts.map(heart => (
                    <motion.div
                        key={heart.id}
                        initial={{ opacity: 1, y: 0, x: 0, scale: 2 }}
                        animate={{ opacity: 0, y: -500, x: heart.x, scale: 2.5 }}
                        transition={{ duration: heart.duration, ease: 'easeOut' }}
                        exit={{ opacity: 0 }}
                        className="absolute left-1/2 bottom-10 text-pink-500 pointer-events-none"
                        style={{ fontSize: '3rem' }}
                    >
                        💖
                    </motion.div>
                ))}
            </AnimatePresence>
        </div>
    );
}
