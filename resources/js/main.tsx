import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import MainPage from './pages/MainPage';
import SignInPage from './components/SignInPage';
import './../css/app.css';

const App = () => (
  <Router>
    <Routes>
      <Route path="/" element={<MainPage />} />
      <Route path="/sign-in" element={<SignInPage />} />
      {/* Add other routes here */}
    </Routes>
  </Router>
);

const root = ReactDOM.createRoot(document.getElementById('app')!);
root.render(<App />);
