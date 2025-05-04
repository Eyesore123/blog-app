export default function InputError({ message }: { message?: string }) {
    if (!message) return null;
    return <p className="text-red-500 text-sm mt-1">{message}</p>;
  }
  