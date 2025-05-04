import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';


const AppRouter : React.FC = () => {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<div>Home</div>} />
                <Route path="/signin" element={<div>Sign In</div>} />
            </Routes>
        </Router>
    );
}

export default AppRouter;