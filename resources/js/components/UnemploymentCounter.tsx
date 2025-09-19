import React, { useState, useEffect } from 'react';
import axiosInstance from './axiosInstance';
import { motion, AnimatePresence } from 'framer-motion';

export default function UnemploymentCounter() {

    const startDate= new Date('2024-06-01T00:00:00'); // Start date
    const [elapsed, setElapsed] = useState('');
    const [hugs, setHugs] = useState(0);
    const [hearts, setHearts] = useState<{ id: number }[]>([]);

    useEffect(() => {
        // Initial hug count fetch
        axiosInstance.get('/hugs/count').then(response => {
            setHugs(response.data.count);
        });
    }, []);

    // Unemployment timer updates

    useEffect(() => {
        const interval = setInterval(() => {
            const now = new Date();
            const diff = now.getTime() - startDate.getTime();

            const hours = Math.floor(diff / 1000 / 60 / 60);

  return (
    <div>
      
    </div>
  )
}
