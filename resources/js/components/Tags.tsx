import React from 'react';

interface TagProps {
  tags: string[];
  onTagClick: (tag: string) => void;
}

const TagComponent: React.FC<TagProps> = ({ tags, onTagClick }) => {
  return (
    <div className="rounded-lg !mt-4 !mb-10">
      <h3 className="font-semibold !mb-4">Tags</h3>
      <div className="flex flex-wrap !gap-2">
        {tags.map((tag) => (
          <button
            key={tag}
            type="button"
            className="bg-gray-200 text-[#5800FF] rounded !px-3 !py-1 text-sm hover:bg-[#5800FF] hover:text-white"
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