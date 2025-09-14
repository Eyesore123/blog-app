import React from 'react';

interface TagProps {
  tags: string[];
  onTagClick: (tag: string) => void;
}

const TagComponent: React.FC<TagProps> = ({ tags, onTagClick }) => {
  return (
    <div className="rounded-lg !mt-4 !mb-10">
      <h3 className="font-semibold !mb-4">Tags</h3>

      {/* Grid layout instead of flex */}
        <div className="grid grid-cols-[repeat(auto-fit,minmax(80px,1fr))] md:grid-cols-[repeat(auto-fit,minmax(100px,1fr))] gap-2">
        {tags.map((tag) => (
          <button
            key={tag}
            type="button"
            className="bg-[#5800FF] text-white rounded !px-3 !py-1 text-xs text-center 
                      truncate hover:bg-white hover:text-[#5800FF] transition"
            title={tag}
            onClick={() => onTagClick(tag)}
          >
            {tag}
          </button>
        ))}
      </div>
    </div>
  );
};

export default TagComponent;
