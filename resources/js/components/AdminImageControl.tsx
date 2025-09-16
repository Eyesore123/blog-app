import React, { useState, useEffect, useRef } from 'react'
import axios from 'axios'

export interface AdminImageInfo {
    name: string;
    url: string;
    size?: number;
    postTitle?: string | null;
}

export default function AdminImageControl() {
  return (
    <div>
      <h2 className='text-xl font-bold mb-4'>Image Control</h2>
    </div>
  )
}
