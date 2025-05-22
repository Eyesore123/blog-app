import{j as e}from"./ConfirmationContext-B_AdGWYB.js";function i({size:r=72}){return e.jsxs("div",{className:"flex items-center justify-center",style:{width:r,height:r,borderRadius:"50%",overflow:"hidden"},children:[e.jsx("style",{children:`
          @keyframes spinner-gradient-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
          @keyframes spinner-pulse {
            0%, 100% {
              filter: drop-shadow(0 0 10px #ffc60088) drop-shadow(0 0 6px #e900ff88);
            }
            50% {
              filter: drop-shadow(0 0 20px #ffc600cc) drop-shadow(0 0 12px #e900ffcc);
            }
          }
          .spinner-gradient-arc {
            transform-origin: 50% 50%;
            animation:
              spinner-gradient-rotate 1.4s linear infinite,
              spinner-pulse 1.2s ease-in-out infinite;
          }
        `}),e.jsxs("svg",{width:r,height:r,viewBox:"0 0 50 50",children:[e.jsx("defs",{children:e.jsxs("linearGradient",{id:"spinner-gradient",x1:"0%",y1:"0%",x2:"100%",y2:"100%",children:[e.jsx("stop",{offset:"0%",stopColor:"#e900ff"}),e.jsx("stop",{offset:"100%",stopColor:"#ffc600"})]})}),e.jsx("circle",{cx:"25",cy:"25",r:"20",fill:"none",stroke:"#5800FF",strokeWidth:"4",opacity:"0"}),e.jsx("circle",{cx:"25",cy:"25",r:"20",fill:"none",stroke:"url(#spinner-gradient)",strokeWidth:"6",strokeDasharray:"80 50",strokeLinecap:"round",className:"spinner-gradient-arc"}),e.jsx("circle",{cx:"25",cy:"25",r:"4",fill:"#ffc600",opacity:"0.9"})]})]})}export{i as S};
