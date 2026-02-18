const scenes = {
  start: {
    text: "終電を逃してしまった。\nあなたは商店街を抜けて帰るか、遠回りの大通りを使うか迷っている。",
    choices: [
      { label: "商店街の近道を進む", next: "alley" },
      { label: "遠回りだけど大通りを選ぶ", next: "avenue" },
    ],
  },
  alley: {
    text: "暗い商店街で、シャッターの前に光る落とし物を見つけた。\n中身を確認する？",
    choices: [
      { label: "落とし物を拾って確認する", next: "wallet" },
      { label: "そのまま通り過ぎる", next: "cat" },
    ],
  },
  avenue: {
    text: "大通りは明るいが、突然雨が降ってきた。\nバス停に寄るか、走って帰るか？",
    choices: [
      { label: "バス停で雨宿りする", next: "friend" },
      { label: "走って帰る", next: "slip" },
    ],
  },
  wallet: {
    text: "中には学生証が入っていた。\n交番に届けたおかげで感謝され、温かい缶コーヒーをもらった。\n【GOOD END】",
    choices: [],
  },
  cat: {
    text: "先へ進むと、迷子の猫が足元に寄ってきた。\n首輪の連絡先に電話すると飼い主が駆けつけ、お礼に送ってくれた。\n【GOOD END】",
    choices: [],
  },
  friend: {
    text: "バス停に着くと、偶然友人に会った。\n相合い傘で話しながら帰る、少し特別な夜になった。\n【NORMAL END】",
    choices: [],
  },
  slip: {
    text: "急いで走ったらマンホールで滑ってしまった。\nびしょ濡れで帰宅して、明日は筋肉痛確定。\n【BAD END】",
    choices: [],
  },
};

const sceneText = document.getElementById("scene-text");
const choicesContainer = document.getElementById("choices");
const restartButton = document.getElementById("restart");

function renderScene(sceneKey) {
  const scene = scenes[sceneKey];

  sceneText.textContent = scene.text;
  choicesContainer.innerHTML = "";

  scene.choices.forEach((choice) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = choice.label;
    button.addEventListener("click", () => renderScene(choice.next));
    choicesContainer.appendChild(button);
  });

  restartButton.hidden = scene.choices.length > 0;
}

restartButton.addEventListener("click", () => renderScene("start"));
renderScene("start");
