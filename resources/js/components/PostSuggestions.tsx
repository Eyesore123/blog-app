import { useState, useEffect } from "react";
import axiosInstance from "../components/axiosInstance";

export default function PostSuggestions() {
  const [topic, setTopic] = useState("");
  const [topics, setTopics] = useState([]);
  const [ideas, setIdeas] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
  const fetchTopics = async () => {
    try {
      const res = await axiosInstance.get("/api/topics");
      setTopics(res.data);
    } catch (err) {
      console.error("Error loading topics", err);
      setTopics([]);
    }
  };
  fetchTopics();
    }, []);


  const fetchIdeas = async () => {
    if (!topic) return;
    setLoading(true);
    try {
      const res = await axiosInstance.post("/posts/suggest-ideas", { topic });
      setIdeas(res.data.ideas.split("\n")); // or parse JSON if AI returns structured
    } catch (err) {
      console.error(err);
      setIdeas([]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <select value={topic} onChange={e => setTopic(e.target.value)}>
        <option value="">Select topic</option>
        {topics.map(t => <option key={t} value={t}>{t}</option>)}
      </select>
      <button onClick={fetchIdeas} disabled={!topic || loading}>
        {loading ? "Generating..." : "Generate Ideas"}
      </button>

      {ideas.length > 0 && (
        <ul className="!mt-4">
          {ideas.map((idea, idx) => <li key={idx}>{idea}</li>)}
        </ul>
      )}
    </div>
  );
}
