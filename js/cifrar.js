const CUSPIDE_PUBLIC_KEY = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApva/wjeQIbxHmipuRPcS
waYh5N7PUPXn6wvuVYIqoozwDAE16XO65wEQx2leeB77HhkBz3FiyxjcBIavQytt
zPKwKeqGe/vu1ETq9a85hCpeB6/v2rAw1amDdHRc2R1DcGVvMRYceaum3Fv8GTtG
Dlbz4b438jn358Y0xPRTeEkpK0NZFHfGsr1AABwOMrXKSs5rvhmj8NIjnVNzq/cR
Rk204xUQuawvWqoAC8A+NNBJIPy6Ysv94CKS4DZm8i/4jCJQ6un9IKhtvs6jNj1G
e6JkMi1UG9biIqVF4IQah/YfOPNLFpjtxdETZfdgRjJLAGr68Hk2766ae9EReUco
xwIDAQAB
-----END PUBLIC KEY-----`;

async function cifrar(textoPlano, llavePublicaPem = CUSPIDE_PUBLIC_KEY) {
  const pemBody = llavePublicaPem
    .replace('-----BEGIN PUBLIC KEY-----', '')
    .replace('-----END PUBLIC KEY-----', '')
    .replace(/\s+/g, '');

  const binaryDer = Uint8Array.from(atob(pemBody), (c) => c.charCodeAt(0));
  const cryptoKey = await window.crypto.subtle.importKey(
    'spki',
    binaryDer.buffer,
    { name: 'RSA-OAEP', hash: 'SHA-256' },
    false,
    ['encrypt']
  );

  const encryptedBuffer = await window.crypto.subtle.encrypt(
    { name: 'RSA-OAEP' },
    cryptoKey,
    new TextEncoder().encode(textoPlano)
  );

  return btoa(String.fromCharCode(...new Uint8Array(encryptedBuffer)));
}

async function cifrarLogin(username, password, llavePublicaPem = CUSPIDE_PUBLIC_KEY) {
  const payload = JSON.stringify({ username, password });
  return cifrar(payload, llavePublicaPem);
}
