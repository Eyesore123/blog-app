import React from 'react'
import { usePage } from '@inertiajs/react'

export default function Header() {
  return (
    <div>
      <div className="headerstyles text-center !py-8 bg-gradient-to-r from-[#5800FF] via-[#E900FF] to-[#FFC600] text-white">
          <h1 className="text-4xl font-bold hover:cursor-pointer" onClick={() => window.location.href = '/'}>Joni's Blog</h1>
        </div>
    </div>
  )
}
