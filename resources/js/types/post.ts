export interface Post {
  id: number;
  title: string;
  content: string;
  topic?: string;
  author?: string;
  created_at?: string;
  updated_at?: string;
  image_url?: string | null;
  _id?: string;
  slug?: string;
  [key: string]: any;
}
