import React, { useState, useEffect } from 'react';
import ReactMarkdown from 'react-markdown';
import { Post } from '@/types/post';
import axiosInstance from './axiosInstance';

interface PostPanelProps {
  allPosts: Post[];
  [key: string]: any;
}

const PostPanel = ({ allPosts }: PostPanelProps) => {
  const [selectedPost, setSelectedPost] = useState<Post | null>(null);
  const [translation, setTranslation] = useState<string>('');
  const [isTranslating, setIsTranslating] = useState(false);
  const [targetLang, setTargetLang] = useState('fi');
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (allPosts.length > 0) {
      setSelectedPost(allPosts[0]);
    }
  }, [allPosts]);

  useEffect(() => {
    setTranslation('');
  }, [selectedPost]);

  async function handleTranslate() {
    if (!selectedPost) return;
    setIsTranslating(true);

    // Prepare Markdown-safe format
    const markdownWrapped = `# ${selectedPost.title}\n\n${selectedPost.content}`;

    const translated = await translateText(markdownWrapped, 'en', targetLang);

    if (translated !== null) {
      setTranslation(translated);
    }

    setIsTranslating(false);
  }

  async function translateText(text: string, sourceLang: string, targetLang: string) {
    try {
      const response = await axiosInstance.post('/translate', {
        q: text,
        source: sourceLang,
        target: targetLang,
      });
      return response.data.translatedText;
    } catch (error: any) {
      console.error('Translation failed:', error?.response?.data || error.message);
      return null;
    }
  }

  async function handleSaveTranslation() {
    if (!selectedPost) return;
    setIsSaving(true);

    // Assume Markdown: first line is title (with #), rest is content
    const lines = translation.trim().split('\n');
    const titleLine = lines.find((line) => line.startsWith('#'));
    const contentLines = lines.slice(titleLine ? 1 : 0).join('\n').trim();

    const translatedTitle = titleLine ? titleLine.replace(/^#\s*/, '') : selectedPost.title;
    const translatedContent = contentLines;

    const updatedTranslations = {
      ...(selectedPost.translations || {}),
      [targetLang]: {
        title: translatedTitle,
        content: translatedContent,
      },
    };

    try {
      await axiosInstance.put(`/posts/${selectedPost.id}/translation`, {
        translations: updatedTranslations,
      });

      alert('Translation saved successfully.');
    } catch (error: any) {
      console.error('Saving translation failed:', error?.response?.data || error.message);
      alert('Failed to save translation.');
    }

    setIsSaving(false);
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 !p-6">
      {/* Left Panel */}
      <aside className="lg:col-span-3 bg-purple-50 border border-purple-100 !p-4 rounded-2xl shadow-sm max-h-[90vh] overflow-y-auto">
        <h2 className="text-xl font-semibold !mb-4 text-purple-700">📚 Posts</h2>
        <ul className="space-y-2">
          {allPosts.map((post) => (
            <li
              key={post.id}
              className={`cursor-pointer !px-3 !py-2 rounded-xl transition-all duration-200 font-medium ${
                selectedPost?.id === post.id
                  ? 'bg-purple-200 text-purple-900 shadow-inner'
                  : 'hover:bg-purple-100 text-purple-800'
              }`}
              onClick={() => setSelectedPost(post)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => e.key === 'Enter' && setSelectedPost(post)}
            >
              {post.title}
            </li>
          ))}
        </ul>
      </aside>

      {/* Middle Panel */}
      <main className="lg:col-span-6 bg-white !p-6 rounded-2xl shadow-md overflow-y-auto max-h-[90vh]">
        {selectedPost ? (
          <>
            <h2 className="text-2xl font-bold !mb-4 text-gray-800">{selectedPost.title}</h2>
            {selectedPost.image_url && (
              <img
                src={selectedPost.image_url}
                alt={selectedPost.title}
                className="w-full h-auto rounded-lg !mb-5 shadow"
              />
            )}
            <div className="prose prose-sm max-w-none !text-black !mb-4 !mt-4 dark:text-gray-200">
              <ReactMarkdown>{selectedPost.content}</ReactMarkdown>
            </div>
            <div className="mt-6 text-sm text-gray-500 border-t !pt-2">
              Created: {selectedPost.created_at && new Date(selectedPost.created_at).toLocaleString()}
              {selectedPost.updated_at && selectedPost.updated_at !== selectedPost.created_at && (
                <div className="italic !mt-1">
                  Updated: {new Date(selectedPost.updated_at).toLocaleString()}
                </div>
              )}
            </div>
          </>
        ) : (
          <p className="text-gray-500 italic">Select a post to view details.</p>
        )}
      </main>

      {/* Right Panel */}
      <section className="lg:col-span-3 bg-gray-50 border border-gray-100 !p-6 rounded-2xl shadow-sm flex flex-col">
        {selectedPost ? (
          <>
            <h2 className="text-xl font-semibold !mb-4 text-gray-700">🌍 Translation</h2>

            <select
              value={targetLang}
              onChange={(e) => setTargetLang(e.target.value)}
              className="!mb-4 !p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 text-black"
            >
              <option value="fi">Finnish</option>
              <option value="sv">Swedish</option>
              <option value="de">German</option>
              <option value="fr">French</option>
              <option value="es">Spanish</option>
            </select>

            <button
              onClick={handleTranslate}
              disabled={isTranslating}
              className="bg-purple-600 text-white !px-4 !py-2 rounded hover:bg-purple-700 disabled:opacity-50 !mb-4"
            >
              {isTranslating ? 'Translating...' : 'Translate'}
            </button>

            {translation ? (
              <>
                <textarea
                  className="w-full h-48 !p-2 rounded border resize-none mb-2"
                  value={translation}
                  onChange={(e) => setTranslation(e.target.value)}
                />

                <button
                  onClick={handleSaveTranslation}
                  disabled={isSaving}
                  className="bg-green-600 text-white !px-4 !py-2 rounded hover:bg-green-700 disabled:opacity-50"
                >
                  {isSaving ? 'Saving...' : 'Save Translation'}
                </button>
              </>
            ) : (
              <p className="italic text-sm text-gray-500 opacity-70">No translation yet.</p>
            )}
          </>
        ) : (
          <p className="text-gray-500 italic">Select a post to view its details.</p>
        )}
      </section>
    </div>
  );
};

export default PostPanel;
